<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { useAppearance } from '@/composables/useAppearance';
import { SensorDataItem } from '@/types';

defineProps<{
    sensorData: Array<SensorDataItem>;
}>();

const { appearance } = useAppearance();
</script>
<template>
    <div class="flex w-full flex-row gap-1">
        <div v-for="(item, index) in sensorData" :key="index" class="bg-controls-active flex w-full min-w-0 flex-col gap-1 rounded-md p-2">
            <div class="flex items-center gap-1">
                <span class="inline-block size-2 rounded-full opacity-75" :style="{ backgroundColor: item.color?.[appearance] }"></span>
                <Label class="truncate text-xs">{{ item.label }}</Label>
            </div>
            <Label class="gap-0 text-base font-bold">
                {{ item.value }}
                <sup v-if="item.unit" class="text-xxs ordinal">{{ item.unit }}</sup>
            </Label>
        </div>
    </div>
</template>
