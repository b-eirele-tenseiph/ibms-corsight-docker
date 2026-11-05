<script setup lang="ts">
import { DashboardTabLayout, EnergyTabLayout, MonitoringTabLayout } from '@/components/admin/dashboard';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useElectricMeters } from '@/composables/useElectricMeters';
import { useLocalStorageRef } from '@/composables/useLocalStorage';
import { AdminLayout } from '@/layouts/admin';
import { LocationWithMeter } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, onMounted, reactive } from 'vue';

defineProps({
    location_with_meters: Array<LocationWithMeter>,
});

const selectedTab = useLocalStorageRef<string>('dashboardActiveTab', 'monitoring');

const gridSettings = computed(() => {
    const breakpoints = [
        { query: '(min-width: 1920px)', columns: 9, cellHeight: 120 },
        { query: '(min-width: 1366px)', columns: 8, cellHeight: 120 },
        { query: '(min-width: 1024px)', columns: 7, cellHeight: 120 },
        { query: '(min-width: 768px)', columns: 5, cellHeight: 150 },
        { query: '(min-width: 640px)', columns: 4, cellHeight: 210 },
    ];
    const matched = breakpoints.find((bp) => useMediaQuery(bp.query).value);
    return matched ? { columns: matched.columns, cellHeight: matched.cellHeight } : { columns: 2, cellHeight: 220 };
});

// Monitoring Tab Layout
const deviceCount = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 0,
                w: 5,
                h: 1,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 0,
                w: 5,
                h: 1,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 0,
                w: 4,
                h: 2,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 0,
                w: 3,
                h: 2,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 0,
                w: 4,
                h: 1,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 0,
                w: 2,
                h: 1,
            },
        },
    ],
});

const rooms = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 2,
                y: 2,
                w: 5,
                h: 6,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 8,
                w: 5,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 6,
                w: 7,
                h: 5,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 5,
                w: 5,
                h: 4,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 8,
                w: 4,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 9,
                w: 2,
                h: 2,
            },
        },
    ],
});

const deviceActive = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 1,
                w: 2,
                h: 4,
            },
        },
        {
            column_number: '8',
            context: {
                x: 3,
                y: 1,
                w: 2,
                h: 4,
            },
        },
        {
            column_number: '7',
            context: {
                x: 4,
                y: 0,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 2,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 1,
                w: 2,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 1,
                w: 2,
                h: 2,
            },
        },
    ],
};

const deviceStatus = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 7,
                y: 0,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '8',
            context: {
                x: 6,
                y: 0,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '7',
            context: {
                x: 4,
                y: 3,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '5',
            context: {
                x: 3,
                y: 0,
                w: 2,
                h: 2,
            },
        },
        {
            column_number: '4',
            context: {
                x: 2,
                y: 1,
                w: 2,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 7,
                w: 2,
                h: 2,
            },
        },
    ],
});

const recentActivity = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 7,
                y: 1,
                w: 2,
                h: 7,
            },
        },
        {
            column_number: '8',
            context: {
                x: 5,
                y: 10,
                w: 3,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 5,
                y: 9,
                w: 3,
                h: 7,
            },
        },
        {
            column_number: '5',
            context: {
                x: 5,
                y: 9,
                w: 2,
                h: 5,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 14,
                w: 4,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 13,
                w: 2,
                h: 2,
            },
        },
    ],
});

const environmentData = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 1,
                w: 2,
                h: 5,
            },
        },
        {
            column_number: '8',
            context: {
                x: 5,
                y: 5,
                w: 3,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 15,
                w: 4,
                h: 4,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 11,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 11,
                w: 4,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 11,
                w: 2,
                h: 2,
            },
        },
    ],
});

const electricityCost = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 5,
                y: 0,
                w: 2,
                h: 4,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 1,
                w: 3,
                h: 4,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 3,
                w: 4,
                h: 4,
            },
        },
        {
            column_number: '5',
            context: {
                x: 2,
                y: 2,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 3,
                w: 4,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 3,
                w: 2,
                h: 2,
            },
        },
    ],
};

const peopleCount = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 2,
                y: 1,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 5,
                w: 5,
                h: 3,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 11,
                w: 4,
                h: 3,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 9,
                w: 3,
                h: 2,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 6,
                w: 4,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 5,
                w: 2,
                h: 2,
            },
        },
    ],
};

// Energy Tab Layout

const electricMeter = reactive({
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 10,
                w: 7,
                h: 6,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 15,
                w: 8,
                h: 6,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 10,
                w: 7,
                h: 6,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 10,
                w: 5,
                h: 5,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 11,
                w: 4,
                h: 5,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 11,
                w: 2,
                h: 5,
            },
        },
    ],
});

const dailyEnergyCost = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 5,
                w: 7,
                h: 5,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 10,
                w: 8,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 7,
                w: 5,
                h: 4,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 7,
                w: 5,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 9,
                w: 4,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 9,
                w: 2,
                h: 2,
            },
        },
    ],
};

const hourlyConsumption = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 0,
                y: 0,
                w: 5,
                h: 4,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 0,
                w: 5,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 0,
                w: 5,
                h: 3,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 0,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 5,
                w: 4,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 5,
                w: 2,
                h: 2,
            },
        },
    ],
};

const monthlyConsumption = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 5,
                y: 0,
                w: 4,
                h: 4,
            },
        },
        {
            column_number: '8',
            context: {
                x: 0,
                y: 6,
                w: 5,
                h: 4,
            },
        },
        {
            column_number: '7',
            context: {
                x: 0,
                y: 4,
                w: 5,
                h: 3,
            },
        },
        {
            column_number: '5',
            context: {
                x: 0,
                y: 4,
                w: 3,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 7,
                w: 4,
                h: 2,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 7,
                w: 2,
                h: 2,
            },
        },
    ],
};

const carbonTracker = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 7,
                y: 5,
                w: 2,
                h: 6,
            },
        },
        {
            column_number: '8',
            context: {
                x: 5,
                y: 0,
                w: 3,
                h: 5,
            },
        },
        {
            column_number: '7',
            context: {
                x: 6,
                y: 0,
                w: 2,
                h: 5,
            },
        },
        {
            column_number: '5',
            context: {
                x: 3,
                y: 0,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 0,
                y: 0,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 0,
                w: 2,
                h: 3,
            },
        },
    ],
};

const powerFactor = {
    positions: [
        {
            column_number: '9',
            context: {
                x: 7,
                y: 2,
                w: 2,
                h: 5,
            },
        },
        {
            column_number: '8',
            context: {
                x: 5,
                y: 6,
                w: 3,
                h: 4,
            },
        },
        {
            column_number: '7',
            context: {
                x: 6,
                y: 6,
                w: 2,
                h: 5,
            },
        },
        {
            column_number: '5',
            context: {
                x: 3,
                y: 4,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '4',
            context: {
                x: 3,
                y: 0,
                w: 2,
                h: 3,
            },
        },
        {
            column_number: '2',
            context: {
                x: 0,
                y: 3,
                w: 2,
                h: 2,
            },
        },
    ],
};

const getWidgetPosition = (data: any) => {
    return data.positions.find((pos: any) => +pos.column_number === gridSettings.value.columns)?.context ?? {};
};

const [
    meterPosition,
    carbonPosition,
    powerPosition,
    electricityPosition,
    energyPosition,
    hourlyPosition,
    monthlyPosition,
    deviceActivePosition,
    environmentalSensorPosition,
    roomOverviewPosition,
    deviceStatusPosition,
    activityPosition,
    deviceCountPosition,
    peopleCountPosition,
] = [
    electricMeter,
    carbonTracker,
    powerFactor,
    electricityCost,
    dailyEnergyCost,
    hourlyConsumption,
    monthlyConsumption,
    deviceActive,
    environmentData,
    rooms,
    deviceStatus,
    recentActivity,
    deviceCount,
    peopleCount,
].map((data) => computed(() => getWidgetPosition(data)));

const { meters, fetchMeters } = useElectricMeters();

onMounted(() => fetchMeters(false));
</script>

<template>
    <Head :title="$t('head.dashboard')" />
    <AdminLayout title="head.dashboard">
        <DashboardTabLayout v-model="selectedTab" class="shrink-0" />

        <ScrollArea class="flex-1 overflow-y-auto pr-1">
            <MonitoringTabLayout
                v-show="selectedTab === 'monitoring'"
                :grid-settings="gridSettings"
                :data="{
                    electricMeter: meters,
                    rooms: rooms,
                }"
                :position="{
                    electricityCostPosition: electricityPosition,
                    deviceCountPosition: deviceCountPosition,
                    roomPosition: roomOverviewPosition,
                    deviceActivePosition: deviceActivePosition,
                    deviceStatusPosition: deviceStatusPosition,
                    recentActivityPosition: activityPosition,
                    environmentDataPosition: environmentalSensorPosition,
                    peopleCountPosition: peopleCountPosition,
                }"
            />

            <EnergyTabLayout
                v-show="selectedTab === 'energy'"
                :grid-settings="gridSettings"
                :data="{
                    electricMeter: meters,
                    rooms: rooms,
                    location_with_meters: location_with_meters,
                }"
                :position="{
                    electricMeterPosition: meterPosition,
                    carbonTrackerPosition: carbonPosition,
                    dailyEnergyCostPosition: energyPosition,
                    hourlyConsumptionPosition: hourlyPosition,
                    monthlyConsumptionPosition: monthlyPosition,
                    powerFactorPosition: powerPosition,
                }"
            />
        </ScrollArea>
    </AdminLayout>
</template>
