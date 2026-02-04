<template>
    <div class="watchtower-layout">
        <Sidebar :stats="{ failed: jobs.data?.length || 0, active_workers: 0 }" />
        
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Failed Jobs</h2>
            </header>

            <div class="jobs-table">
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
                        <tr v-for="job in jobs.data" :key="job.id">
                            <td class="job-id">{{ job.id }}</td>
                            <td class="job-name">{{ getJobName(job) }}</td>
                            <td>
                                <span class="queue-badge">{{ job.queue }}</span>
                            </td>
                            <td class="time">{{ formatTime(job.completed_at) }}</td>
                            <td>{{ job.attempts }}</td>
                            <td class="actions">
                                <button @click="viewException(job)" class="btn btn-sm btn-secondary">
                                    View
                                </button>
                                <button @click="retryJob(job.id)" class="btn btn-sm btn-primary">
                                    Retry
                                </button>
                                <button @click="deleteJob(job.id)" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!jobs.data || jobs.data.length === 0">
                            <td colspan="6" class="empty">No failed jobs 🎉</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="jobs.last_page > 1">
                <button 
                    @click="goToPage(jobs.current_page - 1)" 
                    :disabled="jobs.current_page === 1"
                    class="btn btn-sm btn-secondary"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ jobs.current_page }} of {{ jobs.last_page }}
                </span>
                <button 
                    @click="goToPage(jobs.current_page + 1)" 
                    :disabled="jobs.current_page === jobs.last_page"
                    class="btn btn-sm btn-secondary"
                >
                    Next
                </button>
            </div>

            <!-- Exception Modal -->
            <div v-if="selectedJob" class="modal-overlay" @click.self="selectedJob = null">
                <div class="modal exception-modal">
                    <div class="modal-header">
                        <h3 class="modal-title">Exception Details</h3>
                        <button @click="selectedJob = null" class="close-btn">×</button>
                    </div>
                    <div class="job-info">
                        <strong>{{ getJobName(selectedJob) }}</strong>
                        <span class="queue-badge">{{ selectedJob.queue }}</span>
                    </div>
                    <pre class="exception-trace">{{ selectedJob.exception }}</pre>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../Components/Sidebar.vue';

const props = defineProps({
    jobs: Object,
});

const selectedJob = ref(null);

function getJobName(job) {
    return job.payload?.displayName || 'Unknown Job';
}

function formatTime(timestamp) {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleString();
}

function viewException(job) {
    selectedJob.value = job;
}

function retryJob(id) {
    router.post(`/watchtower/failed/${id}/retry`);
}

function deleteJob(id) {
    if (confirm('Are you sure you want to delete this failed job?')) {
        router.delete(`/watchtower/failed/${id}`);
    }
}

function goToPage(page) {
    router.get('/watchtower/failed', { page }, { preserveState: true });
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
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
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
    max-width: 250px;
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

.time {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}

.actions {
    display: flex;
    gap: 0.5rem;
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

.page-info {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: var(--wt-accent-primary);
    color: white;
}

.btn-secondary {
    background: var(--wt-bg-tertiary);
    color: var(--wt-text-primary);
}

.btn-danger {
    background: var(--wt-accent-danger);
    color: white;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.modal {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1.5rem;
    width: 100%;
    max-width: 800px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: var(--wt-text-secondary);
    font-size: 1.5rem;
    cursor: pointer;
}

.job-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--wt-border);
}

.exception-trace {
    flex: 1;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: monospace;
    font-size: 0.75rem;
    overflow: auto;
    white-space: pre-wrap;
    word-break: break-word;
    color: #f87171;
}
</style>
