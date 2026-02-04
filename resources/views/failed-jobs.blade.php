@extends('watchtower::layout')

@section('title', 'Failed Jobs')

@section('content')
<div x-data="failedJobsPage()">
    <header class="top-bar">
        <h2 class="page-title">Failed Jobs</h2>
    </header>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Job</th>
                    <th>Queue</th>
                    <th>Failed At</th>
                    <th>Attempts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($initialData['jobs']->items() as $job)
                    <tr>
                        <td class="job-name">{{ $job->id }}</td>
                        <td class="job-name">{{ $job->payload['displayName'] ?? 'Unknown Job' }}</td>
                        <td><span class="queue-badge">{{ $job->queue }}</span></td>
                        <td class="time">{{ $job->completed_at?->format('M d, H:i:s') ?? '-' }}</td>
                        <td>{{ $job->attempts }}</td>
                        <td style="display: flex; gap: 0.5rem;">
                            <button @click="viewException({{ json_encode($job) }})" class="btn btn-secondary btn-sm">View</button>
                            <form action="{{ route('watchtower.failed.retry', $job->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Retry</button>
                            </form>
                            <form action="{{ route('watchtower.failed.destroy', $job->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this job record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No failed jobs 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($initialData['jobs']->hasPages())
    <div class="pagination">
        @if($initialData['jobs']->onFirstPage())
            <span class="btn btn-secondary btn-sm" style="opacity: 0.5;">Previous</span>
        @else
            <a href="{{ $initialData['jobs']->previousPageUrl() }}" class="btn btn-secondary btn-sm">Previous</a>
        @endif
        <span class="page-info">Page {{ $initialData['jobs']->currentPage() }} of {{ $initialData['jobs']->lastPage() }}</span>
        @if($initialData['jobs']->hasMorePages())
            <a href="{{ $initialData['jobs']->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next</a>
        @else
            <span class="btn btn-secondary btn-sm" style="opacity: 0.5;">Next</span>
        @endif
    </div>
    @endif

    <!-- Exception Modal -->
    <div x-show="selectedJob" x-cloak class="modal-overlay" @click.self="selectedJob = null">
        <div class="modal" style="max-width: 800px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="modal-title" style="margin: 0;">Exception Details</h3>
                <button @click="selectedJob = null" style="background: none; border: none; color: var(--wt-text-secondary); font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--wt-border);">
                <strong x-text="selectedJob?.payload?.displayName || 'Unknown Job'"></strong>
                <span class="queue-badge" x-text="selectedJob?.queue"></span>
            </div>
            <pre class="error-block" x-text="selectedJob?.exception"></pre>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function failedJobsPage() {
    return {
        selectedJob: null,
        viewException(job) {
            this.selectedJob = job;
        }
    };
}
</script>
@endpush
