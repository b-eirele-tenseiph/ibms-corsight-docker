<script setup lang="ts">
import { Icon } from '@iconify/vue';

defineProps<{
    message?: string | null;
}>();
</script>

<template>
    <div class="flex h-30 flex-col items-center gap-2 rounded-md pt-8 text-red-400 md:h-40">
        <Icon icon="lucide:circle-alert" class="size-8 text-red-400 sm:size-10 md:size-14" />
        <p class="fluid-text-xxs-sm">{{ message }}</p>
    </div>
</template>
