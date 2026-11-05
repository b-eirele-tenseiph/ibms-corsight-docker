<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { WaterMeterData, WaterMeterLocation, WaterMeterStatus } from '@/types';
import dayjs from 'dayjs';
import { ref } from 'vue';
import MeterListLocationFilter from './MeterListLocationFilter.vue';
import MeterListSearch from './MeterListSearch.vue';
import MeterListStatusFilter from './MeterListStatusFilter.vue';

const status = ref<Array<WaterMeterStatus>>([
    {
        id: 1,
        status: 'active',
    },
    {
        id: 2,
        status: 'offline',
    },
]);

const location = ref<Array<WaterMeterLocation>>([
    {
        id: 1,
        location: 'Parking - Level 1',
    },
    {
        id: 2,
        location: 'Building C - Basement',
    },
    {
        id: 3,
        location: 'Outdoor - West Wing',
    },
]);

const meters = ref<Array<WaterMeterData>>([
    {
        meter: 'IUWS-001',
        location: 'Gym - Shower Room',
        usage: 582,
        today_usage: 12,
        flow_rate: 10.3,
        estimated_bill: 274.0,
        last_uplink: '2026-01-12 09:22:34',
    },
    {
        meter: 'IUWS-002',
        location: 'Outdoor - East Wing',
        usage: 602,
        today_usage: 29,
        flow_rate: 21.7,
        estimated_bill: 800.4,
        last_uplink: '2026-01-12 09:22:34',
    },
    {
        meter: 'IUWS-003',
        location: 'Gym - Shower Room',
        usage: 414,
        today_usage: 13,
        flow_rate: 22.9,
        estimated_bill: 302,
        last_uplink: '2026-02-09 10:14:34',
    },
    {
        meter: 'IUWS-004',
        location: 'Parking - Level 2',
        usage: 151,
        today_usage: 37,
        flow_rate: 12.8,
        estimated_bill: 1108.4,
        last_uplink: '2026-02-09 10:14:34',
    },
    {
        meter: 'IUWS-002',
        location: 'Outdoor - East Wing',
        usage: 602,
        today_usage: 29,
        flow_rate: 21.7,
        estimated_bill: 800.4,
        last_uplink: '2026-01-12 09:22:34',
    },
    {
        meter: 'IUWS-003',
        location: 'Gym - Shower Room',
        usage: 414,
        today_usage: 13,
        flow_rate: 22.9,
        estimated_bill: 302,
        last_uplink: '2026-02-09 10:14:34',
    },
    {
        meter: 'IUWS-004',
        location: 'Parking - Level 2',
        usage: 151,
        today_usage: 37,
        flow_rate: 12.8,
        estimated_bill: 1108.4,
        last_uplink: '2026-02-09 10:14:34',
    },
]);

const isActive = (timestamp: string): boolean => dayjs().diff(dayjs.utc(timestamp).tz('Asia/Manila'), 'minute') <= 30;
</script>

<template>
    <Card class="flex h-full flex-col">
        <div class="mb-4 flex w-full items-center justify-between gap-1 px-4">
            <div class="flex w-full flex-col">
                <span class="fluid-text-base-lg font-bold">{{ $t('page.water_monitoring.header.all_water_meters') }}</span>
                <span class="fluid-text-xs-sm text-secondary-text font-medium">{{
                    $t('page.water_monitoring.header.all_water_meters_subtitle')
                }}</span>
            </div>
            <div class="flex gap-2">
                <div class="w-50">
                    <MeterListSearch :query="{ search: '' }" />
                </div>
                <div class="w-36">
                    <MeterListStatusFilter :query="{ status: 'active' }" :item="status" />
                </div>
                <div class="w-40">
                    <MeterListLocationFilter :query="{ location: 'Parking - Level 1' }" :item="location" />
                </div>
            </div>
        </div>

        <div class="min-h-0 flex-1 pr-1.5">
            <ScrollArea class="h-full px-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="meter in meters"
                        :key="meter.meter"
                        class="gap-4 p-5 shadow-xs transition-all duration-300 ease-in-out hover:-translate-y-[3px]"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div
                                    :class="[
                                        isActive(meter.last_uplink) ? 'bg-success' : 'bg-secondary-text/80',
                                        'mt-2 size-2 flex-shrink-0 rounded-full',
                                    ]"
                                />
                                <div class="flex flex-col">
                                    <span class="text-base font-semibold">{{ meter.meter }}</span>
                                    <span class="text-muted-foreground fluid-text-xs-sm">{{ meter.location }}</span>
                                </div>
                            </div>
                            <Badge :variant="isActive(meter.last_uplink) ? 'success' : 'secondary'">
                                {{ isActive(meter.last_uplink) ? $t('label.active') : $t('label.inactive') }}
                            </Badge>
                        </div>

                        <!-- Metrics -->
                        <div class="space-y-2">
                            <div class="fluid-text-xs-sm flex items-center justify-between">
                                <span class="text-muted-foreground">{{ $t('page.water_monitoring.label.usage') }}:</span>
                                <span class="font-semibold">{{ meter.usage }} L</span>
                            </div>
                            <div class="fluid-text-xs-sm flex items-center justify-between">
                                <span class="text-muted-foreground">{{ $t('page.water_monitoring.label.today') }}:</span>
                                <span :class="[isActive(meter.last_uplink) ? 'text-success' : 'text-secondary-text', 'font-semibold']">
                                    {{ meter.today_usage }} L
                                </span>
                            </div>
                            <div class="fluid-text-xs-sm flex items-center justify-between">
                                <span class="text-muted-foreground">{{ $t('page.water_monitoring.label.flow_rate') }}:</span>
                                <span class="font-semibold">{{ meter.flow_rate }} L/min </span>
                            </div>
                        </div>

                        <div class="fluid-text-xs-sm flex items-center justify-between border-t pt-2">
                            <span class="text-muted-foreground">{{ $t('page.water_monitoring.label.estimated_bill') }}:</span>
                            <span class="text-success font-semibold">₱{{ meter.estimated_bill }} </span>
                        </div>
                    </Card>
                </div>
            </ScrollArea>
        </div>
    </Card>
</template>
