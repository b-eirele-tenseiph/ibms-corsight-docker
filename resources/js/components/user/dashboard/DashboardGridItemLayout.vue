<script setup lang="ts">
import { UserGridPositionLayoutItem } from '@/types';

withDefaults(defineProps<UserGridPositionLayoutItem>(), {
    isLocked: false,
});

const modelWidths: Record<string, number> = {
    WS503: 2,
    WS502: 2,
    UC300: 4,
    WoIOSensor: 2,
    'Air Conditioner': 4,
    Others: 4,
    'RB11E-g': 2,
    'RA0715-g': 2,
};

const modelHeights: Record<string, number> = {
    'RB11E-g': 2,
    'RA0715-g': 2,
};
</script>

<template>
    <div
        class="grid-stack-item"
        :gs-w="modelWidths[(data?.model || data?.type) as string] ?? position?.w ?? 1"
        :gs-h="modelHeights[(data?.model || data?.type) as string] ?? position?.h ?? 1"
        :gs-x="position?.x"
        :gs-y="position?.y"
        :gs-id="data.id"
        :data-type="type"
        :gs-no-move="isLocked"
        :gs-lock="isLocked"
    >
        <div class="grid-stack-item-content rounded-xl p-px">
            <slot />
        </div>
    </div>
</template>
