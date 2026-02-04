<template>
    <div class="watchtower-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">🗼 Watchtower</h1>
            </div>
            
            <ul class="nav-links">
                <li>
                    <a :href="route('watchtower.dashboard')" 
                       :class="{ active: isActive('dashboard') }">
                        <span class="icon">📊</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a :href="route('watchtower.jobs.index')"
                       :class="{ active: isActive('jobs') }">
                        <span class="icon">📋</span>
                        Jobs
                    </a>
                </li>
                <li>
                    <a :href="route('watchtower.failed.index')"
                       :class="{ active: isActive('failed') }">
                        <span class="icon">❌</span>
                        Failed Jobs
                        <span v-if="stats.failed > 0" class="badge danger">{{ stats.failed }}</span>
                    </a>
                </li>
                <li>
                    <a :href="route('watchtower.workers.index')"
                       :class="{ active: isActive('workers') }">
                        <span class="icon">⚙️</span>
                        Workers
                        <span class="badge">{{ stats.active_workers }}</span>
                    </a>
                </li>
                <li>
                    <a :href="route('watchtower.metrics.index')"
                       :class="{ active: isActive('metrics') }">
                        <span class="icon">📈</span>
                        Metrics
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Dashboard</h2>
                <div class="status-indicator" :class="polling ? 'active' : 'inactive'">
                    <span class="dot"></span>
                    {{ polling ? 'Live' : 'Paused' }}
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ stats.pending }}</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value processing">{{ stats.processing }}</div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value success">{{ stats.completed }}</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value danger">{{ stats.failed }}</div>
                    <div class="stat-label">Failed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ stats.completed_last_hour }}</div>
                    <div class="stat-label">Last Hour</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ stats.active_workers }}</div>
                    <div class="stat-label">Workers</div>
                </div>
            </div>

            <!-- Recent Jobs -->
            <section class="recent-jobs">
                <h3 class="section-title">Recent Jobs</h3>
                <div class="jobs-table">
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
                            <tr v-for="job in recentJobs" :key="job.id">
                                <td class="job-name">{{ getJobName(job) }}</td>
                                <td>
                                    <span class="queue-badge">{{ job.queue }}</span>
                                </td>
                                <td>
                                    <StatusBadge :status="job.status" />
                                </td>
                                <td class="time">{{ formatTime(job.queued_at) }}</td>
                            </tr>
                            <tr v-if="recentJobs.length === 0">
                                <td colspan="4" class="empty">No jobs yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Active Workers -->
            <section class="workers-section">
                <h3 class="section-title">Active Workers</h3>
                <div class="workers-grid">
                    <div v-for="worker in workers" :key="worker.id" class="worker-card">
                        <div class="worker-header">
                            <span class="worker-id">{{ worker.worker_id.slice(0, 8) }}...</span>
                            <StatusBadge :status="worker.status" />
                        </div>
                        <div class="worker-details">
                            <div class="detail">
                                <span class="label">Queue:</span>
                                <span class="value">{{ worker.queue }}</span>
                            </div>
                            <div class="detail">
                                <span class="label">PID:</span>
                                <span class="value">{{ worker.pid }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="workers.length === 0" class="empty-workers">
                        No active workers
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import StatusBadge from '../Components/StatusBadge.vue';

const props = defineProps({
    stats: Object,
    recentJobs: Array,
    workers: Array,
    pollInterval: {
        type: Number,
        default: 3000,
    },
});

const stats = ref(props.stats);
const recentJobs = ref(props.recentJobs);
const workers = ref(props.workers);
const polling = ref(true);
let pollTimer = null;

function route(name, params = {}) {
    const base = '/watchtower';
    const routes = {
        'watchtower.dashboard': '',
        'watchtower.jobs.index': '/jobs',
        'watchtower.jobs.show': `/jobs/${params.id}`,
        'watchtower.failed.index': '/failed',
        'watchtower.workers.index': '/workers',
        'watchtower.metrics.index': '/metrics',
    };
    return base + (routes[name] || '');
}

function isActive(page) {
    const path = window.location.pathname;
    if (page === 'dashboard') return path === '/watchtower' || path === '/watchtower/';
    return path.includes(`/watchtower/${page}`);
}

function getJobName(job) {
    return job.payload?.displayName || 'Unknown Job';
}

function formatTime(timestamp) {
    if (!timestamp) return '-';
    const date = new Date(timestamp);
    return date.toLocaleTimeString();
}

async function poll() {
    if (!polling.value) return;
    
    try {
        const response = await fetch('/watchtower/api/poll');
        const data = await response.json();
        stats.value = data.stats;
        recentJobs.value = data.recentJobs;
        workers.value = data.workers;
    } catch (error) {
        console.error('Polling error:', error);
    }
}

onMounted(() => {
    pollTimer = setInterval(poll, props.pollInterval);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
});
</script>

<style scoped>
.watchtower-layout {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 240px;
    background: var(--wt-bg-secondary);
    border-right: 1px solid var(--wt-border);
    padding: 1.5rem 0;
}

.sidebar-header {
    padding: 0 1.5rem 1.5rem;
    border-bottom: 1px solid var(--wt-border);
}

.logo {
    font-size: 1.25rem;
    font-weight: 700;
}

.nav-links {
    list-style: none;
    padding: 1rem 0;
}

.nav-links a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: var(--wt-text-secondary);
    text-decoration: none;
    transition: all 0.2s;
}

.nav-links a:hover,
.nav-links a.active {
    background: var(--wt-bg-tertiary);
    color: var(--wt-text-primary);
}

.nav-links .badge {
    margin-left: auto;
    background: var(--wt-bg-tertiary);
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
}

.nav-links .badge.danger {
    background: var(--wt-accent-danger);
}

.main-content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--wt-text-secondary);
}

.status-indicator .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--wt-accent-danger);
}

.status-indicator.active .dot {
    background: var(--wt-accent-success);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1.25rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-value.success { color: var(--wt-accent-success); }
.stat-value.danger { color: var(--wt-accent-danger); }
.stat-value.processing { color: var(--wt-accent-primary); }

.stat-label {
    font-size: 0.875rem;
    color: var(--wt-text-secondary);
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.jobs-table {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    overflow: hidden;
    margin-bottom: 2rem;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--wt-border);
}

th {
    background: var(--wt-bg-tertiary);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--wt-text-secondary);
}

.job-name {
    font-family: monospace;
    font-size: 0.875rem;
}

.queue-badge {
    background: var(--wt-bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.time {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}

.empty {
    text-align: center;
    color: var(--wt-text-secondary);
    padding: 2rem !important;
}

.workers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.worker-card {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1rem;
}

.worker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.worker-id {
    font-family: monospace;
    font-size: 0.875rem;
}

.worker-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.worker-details .detail {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.worker-details .label {
    color: var(--wt-text-secondary);
}

.empty-workers {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    color: var(--wt-text-secondary);
}
</style>
