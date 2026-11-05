<script lang="ts" setup>
defineProps<{
    size?: number;
    bgColor?: string;
    arcColor?: string;
    duration?: string;
}>();
</script>

<template>
    <svg
        :class="'animate-spin'"
        :style="{ animationDuration: duration ?? '1s' }"
        :width="size ?? 80"
        :height="size ?? 80"
        viewBox="0 0 80 80"
        aria-label="Loading"
    >
        <!-- Background Circle -->
        <circle cx="40" cy="40" r="32" :stroke="bgColor ?? '#e5e7eb'" stroke-width="10" fill="none" />
        <!-- Arc (foreground) -->
        <path d="M40 8 a32 32 0 0 1 32 32" :stroke="arcColor ?? '#fbbf24'" stroke-width="10" fill="none" stroke-linecap="round" />
    </svg>
</template>
