<script setup lang="ts">
import { GridPosition } from '@/types';

defineProps<{
    position: GridPosition;
}>();
</script>

<template>
    <div class="grid-stack-item" :gs-w="position.w" :gs-h="position.h" :gs-x="position.x" :gs-y="position.y">
        <div class="grid-stack-item-content p-px">
            <slot />
        </div>
    </div>
</template>
