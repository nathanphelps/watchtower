<?php

namespace NathanPhelps\Watchtower\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use NathanPhelps\Watchtower\Models\Job;

class FailedJobsController extends Controller
{
    /**
     * Display a listing of failed jobs.
     */
    public function index(): Response
    {
        $failedJobs = Job::failed()
            ->orderBy('completed_at', 'desc')
            ->paginate(50);

        return Inertia::render('watchtower::FailedJobs', [
            'jobs' => $failedJobs,
        ]);
    }

    /**
     * Retry a failed job.
     */
    public function retry(int $id): RedirectResponse
    {
        $job = Job::failed()->findOrFail($id);

        // Reset job status to pending for retry
        $job->update([
            'status' => Job::STATUS_PENDING,
            'exception' => null,
            'started_at' => null,
            'completed_at' => null,
            'attempts' => $job->attempts + 1,
        ]);

        // Re-queue the job if we have the payload
        if (! empty($job->payload)) {
            // The actual re-queuing would require deserializing the job
            // For now, we just reset the status and let the user know
            return back()->with('success', "Job {$job->job_id} has been marked for retry.");
        }

        return back()->with('error', 'Unable to retry job: missing payload.');
    }

    /**
     * Delete a failed job record.
     */
    public function destroy(int $id): RedirectResponse
    {
        $job = Job::failed()->findOrFail($id);
        $jobId = $job->job_id;
        $job->delete();

        return back()->with('success', "Job {$jobId} has been deleted.");
    }
}
