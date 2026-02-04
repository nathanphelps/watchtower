<template>
    <div class="watchtower-layout">
        <Sidebar :stats="{ failed: 0, active_workers: 0 }" />
        
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Jobs</h2>
                <div class="filters">
                    <select v-model="filters.status" @change="applyFilters" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                    <select v-model="filters.queue" @change="applyFilters" class="filter-select">
                        <option value="">All Queues</option>
                        <option v-for="queue in queues" :key="queue" :value="queue">{{ queue }}</option>
                    </select>
                    <input 
                        v-model="filters.search" 
                        @keyup.enter="applyFilters"
                        type="text" 
                        placeholder="Search jobs..." 
                        class="search-input"
                    />
                </div>
            </header>

            <div class="jobs-table">
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
                        <tr v-for="job in jobs.data" :key="job.id">
                            <td class="job-id">{{ job.id }}</td>
                            <td class="job-name">{{ getJobName(job) }}</td>
                            <td>
                                <span class="queue-badge">{{ job.queue }}</span>
                            </td>
                            <td>
                                <StatusBadge :status="job.status" />
                            </td>
                            <td>{{ job.attempts }}</td>
                            <td class="time">{{ formatTime(job.queued_at) }}</td>
                            <td class="duration">{{ formatDuration(job) }}</td>
                            <td>
                                <a :href="`/watchtower/jobs/${job.id}`" class="view-link">View</a>
                            </td>
                        </tr>
                        <tr v-if="jobs.data.length === 0">
                            <td colspan="8" class="empty">No jobs found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="jobs.last_page > 1">
                <button 
                    @click="goToPage(jobs.current_page - 1)" 
                    :disabled="jobs.current_page === 1"
                    class="page-btn"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ jobs.current_page }} of {{ jobs.last_page }}
                </span>
                <button 
                    @click="goToPage(jobs.current_page + 1)" 
                    :disabled="jobs.current_page === jobs.last_page"
                    class="page-btn"
                >
                    Next
                </button>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import StatusBadge from '../../Components/StatusBadge.vue';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    jobs: Object,
    queues: Array,
    filters: Object,
});

const filters = reactive({
    status: props.filters?.status || '',
    queue: props.filters?.queue || '',
    search: props.filters?.search || '',
});

function getJobName(job) {
    return job.payload?.displayName || 'Unknown Job';
}

function formatTime(timestamp) {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleString();
}

function formatDuration(job) {
    if (!job.started_at || !job.completed_at) return '-';
    const start = new Date(job.started_at);
    const end = new Date(job.completed_at);
    const seconds = (end - start) / 1000;
    if (seconds < 1) return `${Math.round(seconds * 1000)}ms`;
    return `${seconds.toFixed(2)}s`;
}

function applyFilters() {
    const query = {};
    if (filters.status) query.status = filters.status;
    if (filters.queue) query.queue = filters.queue;
    if (filters.search) query.search = filters.search;
    
    router.get('/watchtower/jobs', query, { preserveState: true });
}

function goToPage(page) {
    const query = { ...filters, page };
    router.get('/watchtower/jobs', query, { preserveState: true });
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
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.filters {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-select,
.search-input {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    color: var(--wt-text-primary);
    font-size: 0.875rem;
}

.search-input {
    min-width: 200px;
}

.jobs-table {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    overflow: hidden;
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

.job-id {
    font-family: monospace;
    font-size: 0.875rem;
    color: var(--wt-text-secondary);
}

.job-name {
    font-family: monospace;
    font-size: 0.875rem;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.queue-badge {
    background: var(--wt-bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.time, .duration {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}

.view-link {
    color: var(--wt-accent-primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.view-link:hover {
    text-decoration: underline;
}

.empty {
    text-align: center;
    color: var(--wt-text-secondary);
    padding: 2rem !important;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.page-btn {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    color: var(--wt-text-primary);
    cursor: pointer;
    transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
    background: var(--wt-bg-tertiary);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}
</style>
