<template>
    <span class="status-badge" :class="statusClass">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const statusClass = computed(() => {
    switch (props.status) {
        case 'pending': return 'pending';
        case 'processing': return 'processing';
        case 'completed': return 'completed';
        case 'failed': return 'failed';
        case 'running': return 'processing';
        case 'paused': return 'pending';
        case 'stopped': return 'stopped';
        default: return 'default';
    }
});

const label = computed(() => {
    return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});
</script>

<style scoped>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 9999px;
    text-transform: capitalize;
}

.pending {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.processing {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
}

.completed {
    background: rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.failed {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
}

.stopped {
    background: rgba(100, 116, 139, 0.2);
    color: #94a3b8;
}

.default {
    background: rgba(100, 116, 139, 0.2);
    color: #94a3b8;
}
</style>
