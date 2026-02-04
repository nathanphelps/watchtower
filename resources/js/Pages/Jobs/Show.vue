<template>
    <div class="watchtower-layout">
        <Sidebar :stats="{ failed: 0, active_workers: 0 }" />
        
        <main class="main-content">
            <header class="top-bar">
                <div class="breadcrumb">
                    <a href="/watchtower/jobs" class="back-link">← Jobs</a>
                    <h2 class="page-title">Job Details</h2>
                </div>
                <StatusBadge :status="job.status" />
            </header>

            <div class="detail-card">
                <h3 class="section-title">Overview</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Job ID</span>
                        <span class="value mono">{{ job.job_id }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Job Class</span>
                        <span class="value mono">{{ job.payload?.displayName || 'Unknown' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Queue</span>
                        <span class="value">
                            <span class="queue-badge">{{ job.queue }}</span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Connection</span>
                        <span class="value">{{ job.connection }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Attempts</span>
                        <span class="value">{{ job.attempts }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Worker</span>
                        <span class="value mono">{{ job.worker?.worker_id?.slice(0, 8) || '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h3 class="section-title">Timestamps</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Queued At</span>
                        <span class="value">{{ formatTime(job.queued_at) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Started At</span>
                        <span class="value">{{ formatTime(job.started_at) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Completed At</span>
                        <span class="value">{{ formatTime(job.completed_at) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Duration</span>
                        <span class="value">{{ formatDuration() }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-card" v-if="job.payload">
                <h3 class="section-title">Payload</h3>
                <pre class="code-block">{{ JSON.stringify(job.payload, null, 2) }}</pre>
            </div>

            <div class="detail-card error-card" v-if="job.exception">
                <h3 class="section-title">Exception</h3>
                <pre class="error-block">{{ job.exception }}</pre>
            </div>
        </main>
    </div>
</template>

<script setup>
import StatusBadge from '../../Components/StatusBadge.vue';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    job: Object,
});

function formatTime(timestamp) {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleString();
}

function formatDuration() {
    if (!props.job.started_at || !props.job.completed_at) return '-';
    const start = new Date(props.job.started_at);
    const end = new Date(props.job.completed_at);
    const seconds = (end - start) / 1000;
    if (seconds < 1) return `${Math.round(seconds * 1000)}ms`;
    return `${seconds.toFixed(2)}s`;
}
</script>

<style scoped>
.watchtower-layout {
    display: flex;
    min-height: 100vh;
}

.main-content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.breadcrumb {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.back-link {
    color: var(--wt-accent-primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.back-link:hover {
    text-decoration: underline;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.detail-card {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--wt-text-secondary);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--wt-text-secondary);
}

.value {
    font-size: 0.875rem;
}

.mono {
    font-family: monospace;
}

.queue-badge {
    background: var(--wt-bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.code-block {
    background: var(--wt-bg-primary);
    border: 1px solid var(--wt-border);
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: monospace;
    font-size: 0.75rem;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-word;
    color: var(--wt-text-secondary);
}

.error-card {
    border-color: var(--wt-accent-danger);
}

.error-block {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: monospace;
    font-size: 0.75rem;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-word;
    color: #f87171;
}
</style>
