@extends('watchtower::layout')

@section('title', 'Metrics')

@section('content')
@php
    $stats = $initialData['stats'];
    $hourlyThroughput = $initialData['hourlyThroughput'];
    $queueDepths = $initialData['queueDepths'];
    $averageDurations = $initialData['averageDurations'];
    $maxThroughput = max(1, $hourlyThroughput->max(fn($h) => $h['completed'] + $h['failed']));
@endphp

<header class="top-bar">
    <h2 class="page-title">Metrics</h2>
</header>

<!-- Overview Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_jobs'] }}</div>
        <div class="stat-label">Total Jobs</div>
    </div>
    <div class="stat-card">
        <div class="stat-value success">{{ $stats['completed'] }}</div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value danger">{{ $stats['failed'] }}</div>
        <div class="stat-label">Failed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['completed_last_hour'] }}</div>
        <div class="stat-label">Last Hour</div>
    </div>
</div>

<!-- Hourly Throughput Chart -->
<div class="chart-container">
    <h3 class="section-title">Hourly Throughput (24h)</h3>
    <div class="bar-chart">
        @foreach($hourlyThroughput as $hour)
            <div class="bar-group">
                <div class="bars">
                    <div class="bar completed" style="height: {{ max(2, (($hour['completed']) / $maxThroughput) * 100) }}%;" title="{{ $hour['completed'] }} completed"></div>
                    <div class="bar failed" style="height: {{ max(2, (($hour['failed']) / $maxThroughput) * 100) }}%;" title="{{ $hour['failed'] }} failed"></div>
                </div>
                <div class="bar-label">{{ $hour['hour'] }}</div>
            </div>
        @endforeach
    </div>
    <div class="chart-legend">
        <span class="legend-item"><span class="legend-color completed"></span> Completed</span>
        <span class="legend-item"><span class="legend-color failed"></span> Failed</span>
    </div>
</div>

<!-- Queue Depths -->
<div class="section">
    <h3 class="section-title">Queue Depths</h3>
    <div class="workers-grid">
        @forelse($queueDepths as $queue => $count)
            <div class="worker-card" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 500;">{{ $queue }}</span>
                <span class="time">{{ $count }} pending</span>
            </div>
        @empty
            <div class="empty" style="background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 1rem; text-align: center;">
                No pending jobs
            </div>
        @endforelse
    </div>
</div>

<!-- Average Durations -->
<div class="chart-container">
    <h3 class="section-title">Average Job Duration by Queue</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Average Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($averageDurations as $queue => $duration)
                    <tr>
                        <td><span class="queue-badge">{{ $queue }}</span></td>
                        <td>
                            @if($duration < 1)
                                {{ round($duration * 1000) }}ms
                            @elseif($duration < 60)
                                {{ number_format($duration, 2) }}s
                            @else
                                {{ number_format($duration / 60, 2) }}m
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="empty">No data available</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
