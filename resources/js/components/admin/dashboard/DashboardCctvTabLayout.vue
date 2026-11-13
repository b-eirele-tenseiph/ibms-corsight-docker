<script setup lang="ts">
import type { CorsightMonitoring } from '@/composables/useCorsightMonitoring';
import { AdminCctvPositionLayout } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack-all.js';
import 'gridstack/dist/gridstack.min.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { CctvDemographicsPanel, CctvDwellTimePanel, CctvFootTrafficPanel, CctvPeopleCountPanel, CctvRecentLogPanel, GridItemLayout } from '.';

const props = defineProps<{
    gridSettings: { columns: number; cellHeight: number };
    position: AdminCctvPositionLayout;
    active: boolean;
    corsight: CorsightMonitoring;
}>();

// Coalesce a burst of camera events into a single background refresh.
const REFRESH_DEBOUNCE_MS = 1000;

const gridRef = ref<HTMLElement>();
const gridInstance = ref<GridStack | null>(null);
const refreshTimer = ref<ReturnType<typeof setTimeout> | null>(null);

// Shared with DashboardTabLayout's toolbar — the page owns the single
// composable instance so the filters and live badge stay in sync.
const {
    filteredEvents,
    filteredDwell,
    peopleCount,
    demographics,
    overallDwellAverage,
    isLoading,
    selectedRange,
    startLiveTicker,
    stopLiveTicker,
    fetchCorsightData,
} = props.corsight;

const cancelScheduledRefresh = () => {
    if (refreshTimer.value) {
        clearTimeout(refreshTimer.value);
        refreshTimer.value = null;
    }
};

const scheduleRefresh = () => {
    cancelScheduledRefresh();
    refreshTimer.value = setTimeout(() => {
        refreshTimer.value = null;
        fetchCorsightData({ silent: true });
    }, REFRESH_DEBOUNCE_MS);
};

// The dashboard data is a computed aggregate, so a fresh camera event acts as
// a "data changed" signal: re-fetch the payload in the background (no
// spinner) to keep the tab live without a manual page reload.
const { stopListening: stopCorsight, listen: listenCorsight } = useEcho('admin.corsight', '.corsight_data.new', () => scheduleRefresh());

onMounted(() => {
    if (gridRef.value) {
        gridInstance.value = GridStack.init(
            {
                column: props.gridSettings.columns,
                margin: 4,
                cellHeight: props.gridSettings.cellHeight,
                float: false,
                disableResize: true,
                staticGrid: true,
                disableDrag: true,
            },
            gridRef.value,
        );
    }

    fetchCorsightData();
    startLiveTicker();
    if (props.active) {
        listenCorsight();
    }
});

onUnmounted(() => {
    stopCorsight();
    stopLiveTicker();
    cancelScheduledRefresh();
});

// The CCTV tab is kept mounted for the whole dashboard (v-show), so only
// subscribe — and pull fresh data when it becomes visible again — while the
// user is actually on this tab.
watch(
    () => props.active,
    (isActive) => {
        if (isActive) {
            listenCorsight();
            fetchCorsightData({ silent: true });
        } else {
            stopCorsight();
            cancelScheduledRefresh();
        }
    },
);

watch(
    () => props.gridSettings,
    (val) => {
        gridInstance.value?.column(val.columns);
        gridInstance.value?.cellHeight(val.cellHeight);
    },
);
</script>

<template>
    <div class="bg-background p-1">
        <!-- KPI strip -->
        <div class="px-1.5 pt-1">
            <CctvPeopleCountPanel :data="peopleCount" :is-loading="isLoading" />
        </div>

        <!-- Grid -->
        <div ref="gridRef" class="grid-stack">
            <GridItemLayout
                :position="{
                    x: position.dwellTimePosition.x,
                    y: position.dwellTimePosition.y,
                    w: position.dwellTimePosition.w,
                    h: position.dwellTimePosition.h,
                }"
            >
                <CctvDwellTimePanel :data="filteredDwell" :average="overallDwellAverage" :is-loading="isLoading" />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.demographicsPosition.x,
                    y: position.demographicsPosition.y,
                    w: position.demographicsPosition.w,
                    h: position.demographicsPosition.h,
                }"
            >
                <CctvDemographicsPanel :data="demographics" :is-loading="isLoading" />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.footTrafficPosition.x,
                    y: position.footTrafficPosition.y,
                    w: position.footTrafficPosition.w,
                    h: position.footTrafficPosition.h,
                }"
            >
                <CctvFootTrafficPanel :data="filteredEvents" :is-loading="isLoading" :range="selectedRange" />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.recentLogPosition.x,
                    y: position.recentLogPosition.y,
                    w: position.recentLogPosition.w,
                    h: position.recentLogPosition.h,
                }"
            >
                <CctvRecentLogPanel :data="filteredEvents" :is-loading="isLoading" />
            </GridItemLayout>
        </div>
    </div>
</template>
