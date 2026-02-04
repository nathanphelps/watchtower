<template>
    <div class="watchtower-layout">
        <Sidebar :stats="{ failed: 0, active_workers: workers.length }" />
        
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Workers</h2>
                <button @click="showStartModal = true" class="btn btn-primary">
                    + Start Worker
                </button>
            </header>

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
                            <span class="value">{{ worker.pid || '-' }}</span>
                        </div>
                        <div class="detail">
                            <span class="label">Supervisor:</span>
                            <span class="value">{{ worker.supervisor }}</span>
                        </div>
                        <div class="detail">
                            <span class="label">Started:</span>
                            <span class="value">{{ formatTime(worker.started_at) }}</span>
                        </div>
                        <div class="detail">
                            <span class="label">Last Heartbeat:</span>
                            <span class="value">{{ formatTime(worker.last_heartbeat) }}</span>
                        </div>
                    </div>
                    <div class="worker-actions">
                        <button 
                            v-if="worker.status === 'running'"
                            @click="pauseWorker(worker.worker_id)"
                            class="btn btn-warning btn-sm"
                        >
                            Pause
                        </button>
                        <button 
                            v-if="worker.status === 'paused'"
                            @click="resumeWorker(worker.worker_id)"
                            class="btn btn-success btn-sm"
                        >
                            Resume
                        </button>
                        <button 
                            v-if="worker.status !== 'stopped'"
                            @click="stopWorker(worker.worker_id)"
                            class="btn btn-danger btn-sm"
                        >
                            Stop
                        </button>
                    </div>
                </div>
                
                <div v-if="workers.length === 0" class="empty-workers">
                    <p>No workers running</p>
                    <button @click="showStartModal = true" class="btn btn-primary">
                        Start a Worker
                    </button>
                </div>
            </div>

            <!-- Start Worker Modal -->
            <div v-if="showStartModal" class="modal-overlay" @click.self="showStartModal = false">
                <div class="modal">
                    <h3 class="modal-title">Start New Worker</h3>
                    <form @submit.prevent="startWorker">
                        <div class="form-group">
                            <label for="queue">Queue</label>
                            <select id="queue" v-model="newWorkerQueue" class="form-input">
                                <option v-for="q in queues" :key="q" :value="q">{{ q }}</option>
                            </select>
                        </div>
                        <div class="modal-actions">
                            <button type="button" @click="showStartModal = false" class="btn btn-secondary">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Start Worker
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import StatusBadge from '../Components/StatusBadge.vue';
import Sidebar from '../Components/Sidebar.vue';

const props = defineProps({
    workers: Array,
    queues: Array,
});

const showStartModal = ref(false);
const newWorkerQueue = ref(props.queues?.[0] || 'default');

function formatTime(timestamp) {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleTimeString();
}

function startWorker() {
    router.post('/watchtower/workers/start', { queue: newWorkerQueue.value }, {
        onSuccess: () => {
            showStartModal.value = false;
        },
    });
}

function stopWorker(workerId) {
    router.post(`/watchtower/workers/${workerId}/stop`);
}

function pauseWorker(workerId) {
    router.post(`/watchtower/workers/${workerId}/pause`);
}

function resumeWorker(workerId) {
    router.post(`/watchtower/workers/${workerId}/resume`);
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

.workers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.worker-card {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1.25rem;
}

.worker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--wt-border);
}

.worker-id {
    font-family: monospace;
    font-size: 0.875rem;
    font-weight: 600;
}

.worker-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.worker-details .detail {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.worker-details .label {
    color: var(--wt-text-secondary);
}

.worker-actions {
    display: flex;
    gap: 0.5rem;
}

.empty-workers {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 3rem;
    text-align: center;
    color: var(--wt-text-secondary);
}

.empty-workers p {
    margin-bottom: 1rem;
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

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: var(--wt-bg-tertiary);
    color: var(--wt-text-primary);
}

.btn-success {
    background: var(--wt-accent-success);
    color: white;
}

.btn-warning {
    background: var(--wt-accent-warning);
    color: white;
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
    max-width: 400px;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--wt-text-secondary);
}

.form-input {
    width: 100%;
    background: var(--wt-bg-primary);
    border: 1px solid var(--wt-border);
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    color: var(--wt-text-primary);
    font-size: 0.875rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
}
</style>
