<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-8 space-y-0.5">
        <h2 class="text-xl font-semibold tracking-tight">{{ $t(title) }}</h2>
        <p v-if="description" class="text-secondary-text text-sm">
            {{ $t(description) }}
        </p>
    </div>
</template>
