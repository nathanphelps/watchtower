<?php

namespace NathanPhelps\Watchtower\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use NathanPhelps\Watchtower\Models\Worker;
use NathanPhelps\Watchtower\Services\WorkerManager;

class WorkersController extends Controller
{
    public function __construct(
        protected WorkerManager $workerManager
    ) {}

    /**
     * Display a listing of workers.
     */
    public function index(): Response
    {
        return Inertia::render('watchtower::Workers', [
            'workers' => $this->workerManager->getAllWorkers(),
            'queues' => config('watchtower.supervisors.default.queue', ['default']),
        ]);
    }

    /**
     * Start a new worker.
     */
    public function start(Request $request): RedirectResponse
    {
        $request->validate([
            'queue' => 'required|string',
        ]);

        $supervisorConfig = config('watchtower.supervisors.default', []);

        $workerId = $this->workerManager->startWorker(
            queue: $request->input('queue'),
            options: [
                'supervisor' => 'default',
                'tries' => $supervisorConfig['tries'] ?? 3,
                'timeout' => $supervisorConfig['timeout'] ?? 60,
                'memory' => $supervisorConfig['memory'] ?? 128,
                'sleep' => $supervisorConfig['sleep'] ?? 3,
            ]
        );

        return back()->with('success', "Worker {$workerId} started successfully.");
    }

    /**
     * Stop a worker.
     */
    public function stop(string $id): RedirectResponse
    {
        $worker = Worker::where('worker_id', $id)->firstOrFail();
        $this->workerManager->stopWorker($id);

        return back()->with('success', "Worker {$id} is stopping.");
    }

    /**
     * Pause a worker.
     */
    public function pause(string $id): RedirectResponse
    {
        $worker = Worker::where('worker_id', $id)->firstOrFail();
        $this->workerManager->pauseWorker($id);

        return back()->with('success', "Worker {$id} is paused.");
    }

    /**
     * Resume a paused worker.
     */
    public function resume(string $id): RedirectResponse
    {
        $worker = Worker::where('worker_id', $id)->firstOrFail();
        $this->workerManager->resumeWorker($id);

        return back()->with('success', "Worker {$id} is resuming.");
    }
}
