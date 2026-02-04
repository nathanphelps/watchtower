<template>
    <div class="watchtower-layout">
        <Sidebar :stats="{ failed: stats.failed, active_workers: stats.active_workers }" />
        
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Metrics</h2>
            </header>

            <!-- Overview Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ stats.total_jobs }}</div>
                    <div class="stat-label">Total Jobs</div>
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
            </div>

            <!-- Hourly Throughput Chart -->
            <section class="chart-section">
                <h3 class="section-title">Hourly Throughput (24h)</h3>
                <div class="chart-container">
                    <div class="bar-chart">
                        <div 
                            v-for="(hour, index) in hourlyThroughput" 
                            :key="index"
                            class="bar-group"
                        >
                            <div class="bars">
                                <div 
                                    class="bar completed" 
                                    :style="{ height: getBarHeight(hour.completed) }"
                                    :title="`${hour.completed} completed`"
                                ></div>
                                <div 
                                    class="bar failed" 
                                    :style="{ height: getBarHeight(hour.failed) }"
                                    :title="`${hour.failed} failed`"
                                ></div>
                            </div>
                            <div class="bar-label">{{ hour.hour }}</div>
                        </div>
                    </div>
                    <div class="chart-legend">
                        <span class="legend-item">
                            <span class="legend-color completed"></span>
                            Completed
                        </span>
                        <span class="legend-item">
                            <span class="legend-color failed"></span>
                            Failed
                        </span>
                    </div>
                </div>
            </section>

            <!-- Queue Depths -->
            <section class="metrics-section">
                <h3 class="section-title">Queue Depths</h3>
                <div class="queue-depths">
                    <div 
                        v-for="(count, queue) in queueDepths" 
                        :key="queue"
                        class="queue-depth-card"
                    >
                        <span class="queue-name">{{ queue }}</span>
                        <span class="queue-count">{{ count }} pending</span>
                    </div>
                    <div v-if="Object.keys(queueDepths).length === 0" class="empty-message">
                        No pending jobs
                    </div>
                </div>
            </section>

            <!-- Average Durations -->
            <section class="metrics-section">
                <h3 class="section-title">Average Job Duration by Queue</h3>
                <div class="durations-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Queue</th>
                                <th>Average Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(duration, queue) in averageDurations" :key="queue">
                                <td>
                                    <span class="queue-badge">{{ queue }}</span>
                                </td>
                                <td>{{ formatDuration(duration) }}</td>
                            </tr>
                            <tr v-if="Object.keys(averageDurations).length === 0">
                                <td colspan="2" class="empty">No data available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Sidebar from '../Components/Sidebar.vue';

const props = defineProps({
    stats: Object,
    hourlyThroughput: Array,
    queueDepths: Object,
    averageDurations: Object,
});

const maxThroughput = computed(() => {
    if (!props.hourlyThroughput) return 1;
    return Math.max(
        ...props.hourlyThroughput.map(h => h.completed + h.failed),
        1
    );
});

function getBarHeight(value) {
    const percentage = (value / maxThroughput.value) * 100;
    return `${Math.max(percentage, 2)}%`;
}

function formatDuration(seconds) {
    if (seconds < 1) return `${Math.round(seconds * 1000)}ms`;
    if (seconds < 60) return `${seconds.toFixed(2)}s`;
    return `${(seconds / 60).toFixed(2)}m`;
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
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
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

.stat-label {
    font-size: 0.875rem;
    color: var(--wt-text-secondary);
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.chart-section,
.metrics-section {
    background: var(--wt-bg-secondary);
    border: 1px solid var(--wt-border);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.chart-container {
    height: 200px;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 0.25rem;
    height: 160px;
    padding-bottom: 1.5rem;
}

.bar-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.bars {
    flex: 1;
    display: flex;
    gap: 2px;
    align-items: flex-end;
    width: 100%;
}

.bar {
    flex: 1;
    min-height: 2px;
    border-radius: 2px 2px 0 0;
    transition: height 0.3s ease;
}

.bar.completed {
    background: var(--wt-accent-success);
}

.bar.failed {
    background: var(--wt-accent-danger);
}

.bar-label {
    font-size: 0.625rem;
    color: var(--wt-text-secondary);
    margin-top: 0.5rem;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 0.5rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--wt-text-secondary);
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.legend-color.completed {
    background: var(--wt-accent-success);
}

.legend-color.failed {
    background: var(--wt-accent-danger);
}

.queue-depths {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.queue-depth-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--wt-bg-primary);
    border: 1px solid var(--wt-border);
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
}

.queue-name {
    font-weight: 500;
}

.queue-count {
    color: var(--wt-text-secondary);
    font-size: 0.875rem;
}

.empty-message {
    color: var(--wt-text-secondary);
    text-align: center;
    padding: 1rem;
}

.durations-table {
    overflow: hidden;
    border-radius: 0.375rem;
    border: 1px solid var(--wt-border);
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

.queue-badge {
    background: var(--wt-bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.empty {
    text-align: center;
    color: var(--wt-text-secondary);
    padding: 1rem !important;
}
</style>
