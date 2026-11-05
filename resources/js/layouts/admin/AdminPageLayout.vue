<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';
const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        :class="
            cn(
                'border-page-border bg-page-background mx-2 mt-2 mb-1 flex h-svh flex-col gap-4 overflow-auto rounded-xl border p-6 shadow-sm',
                props.class,
            )
        "
    >
        <slot />
    </div>
</template>
