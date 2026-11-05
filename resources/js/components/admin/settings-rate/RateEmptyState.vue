<script setup lang="ts">
import { Icon } from '@iconify/vue';

defineProps<{
    icon: string;
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center py-6">
        <div class="bg-accent/40 mb-4 rounded-full p-4">
            <Icon :icon="icon" class="size-6" />
        </div>
        <h3 class="fluid-text-base-lg text-primary-text mb-2 font-semibold">{{ $t(title) }}</h3>
        <p class="fluid-text-xs-sm text-secondary-text max-w-sm">{{ $t(description) }}</p>
    </div>
</template>
