<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { TopWaterMeterConsumer } from '@/types';
import { ref } from 'vue';

const meters = ref<Array<TopWaterMeterConsumer>>([
    {
        id: 1,
        meter: 'Meter 024',
        location: 'Parking - Level 1',
        usage: 539,
    },
    {
        id: 2,
        meter: 'Meter 043',
        location: 'Building C - Basement',
        usage: 514,
    },
    {
        id: 3,
        meter: 'Meter 046',
        location: 'Outdoor - West Wing',
        usage: 500,
    },
    {
        id: 4,
        meter: 'Meter 033',
        location: 'Building C - 2nd Floor',
        usage: 498,
    },
    {
        id: 5,
        meter: 'Meter 005',
        location: 'Gym - Shower Room',
        usage: 489,
    },
    {
        id: 2,
        meter: 'Meter 043',
        location: 'Building C - Basement',
        usage: 514,
    },
    {
        id: 3,
        meter: 'Meter 046',
        location: 'Outdoor - West Wing',
        usage: 500,
    },
    {
        id: 4,
        meter: 'Meter 033',
        location: 'Building C - 2nd Floor',
        usage: 498,
    },
    {
        id: 5,
        meter: 'Meter 005',
        location: 'Gym - Shower Room',
        usage: 489,
    },
    {
        id: 2,
        meter: 'Meter 043',
        location: 'Building C - Basement',
        usage: 514,
    },
    {
        id: 3,
        meter: 'Meter 046',
        location: 'Outdoor - West Wing',
        usage: 500,
    },
    {
        id: 4,
        meter: 'Meter 033',
        location: 'Building C - 2nd Floor',
        usage: 498,
    },
    {
        id: 5,
        meter: 'Meter 005',
        location: 'Gym - Shower Room',
        usage: 489,
    },
]);
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="mb-4 flex w-full flex-col">
            <span class="fluid-text-base-lg font-bold">{{ $t('page.water_monitoring.header.top_consumers') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-medium">{{ $t('page.water_monitoring.header.top_consumers_subtitle') }}</span>
        </div>

        <div class="min-h-0 flex-1">
            <ScrollArea class="h-full pr-4">
                <div class="flex flex-col gap-4">
                    <div v-for="(meter, index) in meters" :key="meter.id" class="flex items-center gap-4">
                        <div
                            :class="[
                                'flex size-10 shrink-0 items-center justify-center rounded-full',
                                index === 0 ? 'bg-primary/10' : 'bg-secondary/40',
                            ]"
                        >
                            <span :class="['fluid-text-xs-sm font-semibold', index === 0 ? 'text-primary' : 'text-secondary-text']">
                                {{ index + 1 }}
                            </span>
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span class="fluid-text-xs-sm font-semibold">{{ meter.meter }}</span>
                            <span class="fluid-text-xs-sm text-secondary-text">{{ meter.location }}</span>
                        </div>

                        <div class="shrink-0">
                            <span class="fluid-text-base-lg font-bold">{{ meter.usage }}</span>
                            <sup class="text-secondary-text ml-1">m³</sup>
                        </div>
                    </div>
                </div>
            </ScrollArea>
        </div>
    </Card>
</template>
