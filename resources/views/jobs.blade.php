@extends('watchtower::layout')

@section('title', 'Jobs')

@section('content')
<div x-data="jobsPage()">
    <header class="top-bar">
        <h2 class="page-title">Jobs</h2>
        <div class="filters">
            <select x-model="filters.status" @change="applyFilters()" class="filter-select">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
            <select x-model="filters.queue" @change="applyFilters()" class="filter-select">
                <option value="">All Queues</option>
                @foreach($initialData['queues'] as $queue)
                    <option value="{{ $queue }}">{{ $queue }}</option>
                @endforeach
            </select>
            <input type="text" x-model="filters.search" @keyup.enter="applyFilters()" placeholder="Search jobs..." class="filter-input" />
        </div>
    </header>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Job</th>
                    <th>Queue</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Queued At</th>
                    <th>Duration</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($initialData['jobs']->items() as $job)
                    <tr>
                        <td class="job-name">{{ $job->id }}</td>
                        <td class="job-name">{{ $job->payload['displayName'] ?? 'Unknown Job' }}</td>
                        <td><span class="queue-badge">{{ $job->queue }}</span></td>
                        <td><span class="status-badge status-{{ $job->status }}">{{ $job->status }}</span></td>
                        <td>{{ $job->attempts }}</td>
                        <td class="time">{{ $job->queued_at?->format('M d, H:i:s') ?? '-' }}</td>
                        <td class="time">
                            @if($job->started_at && $job->completed_at)
                                {{ number_format($job->started_at->diffInMilliseconds($job->completed_at) / 1000, 2) }}s
                            @else
                                -
                            @endif
                        </td>
                        <td><a href="{{ route('watchtower.jobs.show', $job->id) }}" class="link">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No jobs found</td></tr>
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
</div>
@endsection

@push('scripts')
<script>
function jobsPage() {
    return {
        filters: {
            status: '{{ request('status', '') }}',
            queue: '{{ request('queue', '') }}',
            search: '{{ request('search', '') }}'
        },
        applyFilters() {
            const params = new URLSearchParams();
            if (this.filters.status) params.set('status', this.filters.status);
            if (this.filters.queue) params.set('queue', this.filters.queue);
            if (this.filters.search) params.set('search', this.filters.search);
            window.location.href = '{{ route("watchtower.jobs.index") }}?' + params.toString();
        }
    };
}
</script>
@endpush
