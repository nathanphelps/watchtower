@extends('watchtower::layout')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="startPolling()">
    <header class="top-bar">
        <h2 class="page-title">Dashboard</h2>
        <div class="status-indicator" :class="{ 'active': polling }">
            <span class="status-dot"></span>
            <span x-text="polling ? 'Live' : 'Paused'"></span>
        </div>
    </header>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" x-text="stats.pending">0</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-value processing" x-text="stats.processing">0</div>
            <div class="stat-label">Processing</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success" x-text="stats.completed">0</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value danger" x-text="stats.failed">0</div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" x-text="stats.completed_last_hour">0</div>
            <div class="stat-label">Last Hour</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" x-text="stats.active_workers">0</div>
            <div class="stat-label">Workers</div>
        </div>
    </div>

    <!-- Recent Jobs -->
    <section class="section">
        <h3 class="section-title">Recent Jobs</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Queue</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="job in recentJobs" :key="job.id">
                        <tr>
                            <td class="job-name" x-text="getJobName(job)"></td>
                            <td><span class="queue-badge" x-text="job.queue"></span></td>
                            <td><span class="status-badge" :class="'status-' + job.status" x-text="job.status"></span></td>
                            <td class="time" x-text="formatTime(job.queued_at)"></td>
                        </tr>
                    </template>
                    <tr x-show="recentJobs.length === 0">
                        <td colspan="4" class="empty">No jobs yet</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Active Workers -->
    <section class="section">
        <h3 class="section-title">Active Workers</h3>
        <div class="workers-grid">
            <template x-for="worker in workers" :key="worker.id">
                <div class="worker-card">
                    <div class="worker-header">
                        <span class="worker-id" x-text="worker.worker_id.slice(0, 8) + '...'"></span>
                        <span class="status-badge" :class="'status-' + worker.status" x-text="worker.status"></span>
                    </div>
                    <div class="worker-details">
                        <div class="worker-detail">
                            <span class="label">Queue:</span>
                            <span x-text="worker.queue"></span>
                        </div>
                        <div class="worker-detail">
                            <span class="label">PID:</span>
                            <span x-text="worker.pid || '-'"></span>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="workers.length === 0" class="empty" style="background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 2rem; text-align: center;">
                No active workers
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function dashboard() {
    return {
        stats: @json($initialData['stats']),
        recentJobs: @json($initialData['recentJobs']),
        workers: @json($initialData['workers']),
        polling: true,
        pollInterval: {{ $initialData['pollInterval'] }},
        
        startPolling() {
            setInterval(() => this.poll(), this.pollInterval);
        },
        
        async poll() {
            if (!this.polling) return;
            try {
                const response = await fetch('{{ route("watchtower.dashboard.poll") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.stats = data.stats;
                this.recentJobs = data.recentJobs;
                this.workers = data.workers;
            } catch (e) {
                console.error('Poll error:', e);
            }
        },
        
        getJobName(job) {
            return job.payload?.displayName || 'Unknown Job';
        },
        
        formatTime(timestamp) {
            if (!timestamp) return '-';
            return new Date(timestamp).toLocaleTimeString();
        }
    };
}
</script>
@endpush
