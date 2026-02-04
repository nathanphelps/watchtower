<?php

namespace NathanPhelps\Watchtower\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use NathanPhelps\Watchtower\Models\Job;

class JobsController extends Controller
{
    /**
     * Display a listing of jobs.
     */
    public function index(Request $request): Response
    {
        $query = Job::query()->orderBy('queued_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->withStatus($request->input('status'));
        }

        if ($request->filled('queue')) {
            $query->onQueue($request->input('queue'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('job_id', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_EXTRACT(payload, '$.displayName') LIKE ?", ["%{$search}%"]);
            });
        }

        $jobs = $query->paginate(50);

        // Get unique queues for filter dropdown
        $queues = Job::select('queue')->distinct()->pluck('queue');

        return Inertia::render('watchtower::Jobs/Index', [
            'jobs' => $jobs,
            'queues' => $queues,
            'filters' => [
                'status' => $request->input('status'),
                'queue' => $request->input('queue'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    /**
     * Display the specified job.
     */
    public function show(int $id): Response
    {
        $job = Job::with('worker')->findOrFail($id);

        return Inertia::render('watchtower::Jobs/Show', [
            'job' => $job,
        ]);
    }
}
