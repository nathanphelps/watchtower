<?php

namespace NathanPhelps\Watchtower\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use NathanPhelps\Watchtower\Models\Job;
use NathanPhelps\Watchtower\Models\Worker;

class MetricsCollector
{
    /**
     * Get overall job statistics.
     */
    public function getStats(): array
    {
        $now = now();

        return [
            'total_jobs' => Job::count(),
            'pending' => Job::withStatus(Job::STATUS_PENDING)->count(),
            'processing' => Job::withStatus(Job::STATUS_PROCESSING)->count(),
            'completed' => Job::withStatus(Job::STATUS_COMPLETED)->count(),
            'failed' => Job::withStatus(Job::STATUS_FAILED)->count(),
            'completed_last_hour' => Job::withStatus(Job::STATUS_COMPLETED)
                ->where('completed_at', '>=', $now->subHour())
                ->count(),
            'failed_last_hour' => Job::withStatus(Job::STATUS_FAILED)
                ->where('completed_at', '>=', $now->copy()->subHour())
                ->count(),
            'active_workers' => Worker::running()->count(),
            'paused_workers' => Worker::withStatus(Worker::STATUS_PAUSED)->count(),
        ];
    }

    /**
     * Get hourly throughput for the last 24 hours.
     */
    public function getHourlyThroughput(): Collection
    {
        $hours = collect();

        for ($i = 23; $i >= 0; $i--) {
            $startHour = now()->subHours($i)->startOfHour();
            $endHour = now()->subHours($i)->endOfHour();

            $completed = Job::withStatus(Job::STATUS_COMPLETED)
                ->whereBetween('completed_at', [$startHour, $endHour])
                ->count();

            $failed = Job::withStatus(Job::STATUS_FAILED)
                ->whereBetween('completed_at', [$startHour, $endHour])
                ->count();

            $hours->push([
                'hour' => $startHour->format('H:i'),
                'completed' => $completed,
                'failed' => $failed,
            ]);
        }

        return $hours;
    }

    /**
     * Get current queue depths (pending jobs per queue).
     */
    public function getQueueDepths(): Collection
    {
        return Job::select('queue', DB::raw('COUNT(*) as count'))
            ->withStatus(Job::STATUS_PENDING)
            ->groupBy('queue')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->queue => $item->count]);
    }

    /**
     * Get average job durations by queue.
     */
    public function getAverageDurations(): Collection
    {
        return Job::select(
            'queue',
            DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration')
        )
            ->withStatus(Job::STATUS_COMPLETED)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->groupBy('queue')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->queue => round($item->avg_duration ?? 0, 2),
            ]);
    }

    /**
     * Get jobs processed per minute for the last 10 minutes.
     */
    public function getRecentThroughput(): Collection
    {
        $minutes = collect();

        for ($i = 9; $i >= 0; $i--) {
            $startMinute = now()->subMinutes($i)->startOfMinute();
            $endMinute = now()->subMinutes($i)->endOfMinute();

            $count = Job::whereIn('status', [Job::STATUS_COMPLETED, Job::STATUS_FAILED])
                ->whereBetween('completed_at', [$startMinute, $endMinute])
                ->count();

            $minutes->push([
                'minute' => $startMinute->format('H:i'),
                'count' => $count,
            ]);
        }

        return $minutes;
    }

    /**
     * Get worker statistics.
     */
    public function getWorkerStats(): array
    {
        $workers = Worker::all();

        return [
            'total' => $workers->count(),
            'running' => $workers->where('status', Worker::STATUS_RUNNING)->count(),
            'paused' => $workers->where('status', Worker::STATUS_PAUSED)->count(),
            'stopped' => $workers->where('status', Worker::STATUS_STOPPED)->count(),
            'healthy' => $workers->filter(fn ($w) => $w->isHealthy())->count(),
        ];
    }
}
