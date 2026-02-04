@extends('watchtower::layout')

@section('title', 'Job Details')

@section('content')
@php $job = $initialData['job']; @endphp

<header class="top-bar">
    <div>
        <a href="{{ route('watchtower.jobs.index') }}" class="link" style="font-size: 0.875rem;">← Back to Jobs</a>
        <h2 class="page-title" style="margin-top: 0.5rem;">Job Details</h2>
    </div>
    <span class="status-badge status-{{ $job->status }}">{{ $job->status }}</span>
</header>

<div class="section">
    <div class="chart-container">
        <h3 class="section-title">Overview</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <div class="stat-label">Job ID</div>
                <div class="job-name">{{ $job->job_id }}</div>
            </div>
            <div>
                <div class="stat-label">Job Class</div>
                <div class="job-name">{{ $job->payload['displayName'] ?? 'Unknown' }}</div>
            </div>
            <div>
                <div class="stat-label">Queue</div>
                <div><span class="queue-badge">{{ $job->queue }}</span></div>
            </div>
            <div>
                <div class="stat-label">Connection</div>
                <div>{{ $job->connection }}</div>
            </div>
            <div>
                <div class="stat-label">Attempts</div>
                <div>{{ $job->attempts }}</div>
            </div>
            <div>
                <div class="stat-label">Worker</div>
                <div class="job-name">{{ $job->worker?->worker_id ? substr($job->worker->worker_id, 0, 8) . '...' : '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="chart-container">
        <h3 class="section-title">Timestamps</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <div class="stat-label">Queued At</div>
                <div>{{ $job->queued_at?->format('M d, Y H:i:s') ?? '-' }}</div>
            </div>
            <div>
                <div class="stat-label">Started At</div>
                <div>{{ $job->started_at?->format('M d, Y H:i:s') ?? '-' }}</div>
            </div>
            <div>
                <div class="stat-label">Completed At</div>
                <div>{{ $job->completed_at?->format('M d, Y H:i:s') ?? '-' }}</div>
            </div>
            <div>
                <div class="stat-label">Duration</div>
                <div>
                    @if($job->started_at && $job->completed_at)
                        {{ number_format($job->started_at->diffInMilliseconds($job->completed_at) / 1000, 2) }}s
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($job->payload)
<div class="section">
    <div class="chart-container">
        <h3 class="section-title">Payload</h3>
        <pre class="code-block">{{ json_encode($job->payload, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endif

@if($job->exception)
<div class="section">
    <div class="chart-container" style="border-color: var(--wt-accent-danger);">
        <h3 class="section-title">Exception</h3>
        <pre class="error-block">{{ $job->exception }}</pre>
    </div>
</div>
@endif
@endsection
