@extends('watchtower::layout')

@section('title', 'Workers')

@section('content')
<div x-data="workersPage()">
    <header class="top-bar">
        <h2 class="page-title">Workers</h2>
        <button @click="showStartModal = true" class="btn btn-primary">+ Start Worker</button>
    </header>

    <div class="workers-grid">
        @forelse($initialData['workers'] as $worker)
            <div class="worker-card">
                <div class="worker-header">
                    <span class="worker-id">{{ substr($worker->worker_id, 0, 8) }}...</span>
                    <span class="status-badge status-{{ $worker->status }}">{{ $worker->status }}</span>
                </div>
                <div class="worker-details">
                    <div class="worker-detail">
                        <span class="label">Queue:</span>
                        <span>{{ $worker->queue }}</span>
                    </div>
                    <div class="worker-detail">
                        <span class="label">PID:</span>
                        <span>{{ $worker->pid ?? '-' }}</span>
                    </div>
                    <div class="worker-detail">
                        <span class="label">Supervisor:</span>
                        <span>{{ $worker->supervisor }}</span>
                    </div>
                    <div class="worker-detail">
                        <span class="label">Started:</span>
                        <span>{{ $worker->started_at?->format('H:i:s') ?? '-' }}</span>
                    </div>
                    <div class="worker-detail">
                        <span class="label">Last Heartbeat:</span>
                        <span>{{ $worker->last_heartbeat?->format('H:i:s') ?? '-' }}</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    @if($worker->status === 'running')
                        <form action="{{ route('watchtower.workers.pause', $worker->worker_id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">Pause</button>
                        </form>
                    @endif
                    @if($worker->status === 'paused')
                        <form action="{{ route('watchtower.workers.resume', $worker->worker_id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Resume</button>
                        </form>
                    @endif
                    @if($worker->status !== 'stopped')
                        <form action="{{ route('watchtower.workers.stop', $worker->worker_id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Stop</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty" style="background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 3rem; text-align: center; grid-column: 1 / -1;">
                <p style="margin-bottom: 1rem;">No workers running</p>
                <button @click="showStartModal = true" class="btn btn-primary">Start a Worker</button>
            </div>
        @endforelse
    </div>

    <!-- Start Worker Modal -->
    <div x-show="showStartModal" x-cloak class="modal-overlay" @click.self="showStartModal = false">
        <div class="modal">
            <h3 class="modal-title">Start New Worker</h3>
            <form action="{{ route('watchtower.workers.start') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="queue">Queue</label>
                    <select id="queue" name="queue" class="form-input">
                        @foreach($initialData['queues'] as $queue)
                            <option value="{{ $queue }}">{{ $queue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" @click="showStartModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Worker</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function workersPage() {
    return {
        showStartModal: false
    };
}
</script>
@endpush
