<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{
    type?: 'error' | 'success' | 'info' | 'warning';
    message: string;
}>();

const variants = {
    error: {
        icon: 'lucide:triangle-alert',
        class: 'bg-error-bg/80 text-error-text border-error-border/80',
    },
    success: {
        icon: 'lucide:circle-check',
        class: 'bg-success-bg/80 text-success-text border-success-border/80',
    },
    info: {
        icon: 'lucide:info',
        class: 'bg-info-bg/80 text-info-text border-info-border/80',
    },
    warning: {
        icon: 'lucide:circle-alert',
        class: 'bg-warning-bg/80 text-warning-text border-warning-border/80',
    },
};

const variant = computed(() => variants[props.type ?? 'info']);
</script>

<template>
    <div :class="`flex items-center gap-2 rounded-md border px-4 py-3 ${variant.class}`">
        <Icon :icon="variant.icon" class="size-4 shrink-0" />
        <span class="fluid-text-xs-sm font-medium">{{ $te(message) ? $t(message) : message }}</span>
    </div>
</template>
