<template>
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
</template>

<script setup>
defineProps({
    stats: {
        type: Object,
        default: () => ({ failed: 0, active_workers: 0 }),
    },
});

function route(name) {
    const base = '/watchtower';
    const routes = {
        'watchtower.dashboard': '',
        'watchtower.jobs.index': '/jobs',
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
</script>

<style scoped>
.sidebar {
    width: 240px;
    background: var(--wt-bg-secondary);
    border-right: 1px solid var(--wt-border);
    padding: 1.5rem 0;
    flex-shrink: 0;
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
</style>
