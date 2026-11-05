<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import { useWindowSize } from '@vueuse/core';
import { computed } from 'vue';

const { width } = useWindowSize();
const breakpoints = [
    { w: 420, c: 2, h: 150 },
    { w: 560, c: 3, h: 150 },
    { w: 720, c: 4, h: 150 },
    { w: 920, c: 5, h: 160 },
    { w: 1180, c: 6, h: 170 },
    { w: 1280, c: 7, h: 160 },
    { w: 1440, c: 8, h: 160 },
    { w: 1600, c: 9, h: 160 },
    { w: 1920, c: 10, h: 160 },
    { w: 2560, c: 12, h: 170 },
];

const currentBreakpoint = computed(() => breakpoints.findLast((bp) => width.value >= bp.w) ?? breakpoints[0]);
const currentCols = computed(() => currentBreakpoint.value.c);
const currentHeight = computed(() => currentBreakpoint.value.h);

const rows = 5;
const skeletonItems = computed(() => {
    const cols = currentCols.value;
    const items: Array<{ w: number }> = [];
    for (let r = 0; r < rows; r++) {
        let remaining = cols;
        while (remaining > 0) {
            const max = Math.min(remaining, 4);
            const w = Math.max(1, Math.floor(Math.random() * max) + 1);
            items.push({ w });
            remaining -= w;
        }
    }
    return items;
});
</script>

<template>
    <div class="grid gap-2.5 p-2" :style="{ gridTemplateColumns: `repeat(${currentCols}, minmax(0, 1fr))` }">
        <div v-for="(item, i) in skeletonItems" :key="i" :style="{ gridColumn: `span ${item.w} / span ${item.w}` }">
            <Skeleton class="w-full rounded-lg" :style="{ height: `${currentHeight}px` }" />
        </div>
    </div>
</template>
