<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useCorsightMonitoring } from '@/composables/useCorsightMonitoring';
import { AdminCctvPositionLayout } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import dayjs from 'dayjs';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack-all.js';
import 'gridstack/dist/gridstack.min.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { CctvDemographicsPanel, CctvDwellTimePanel, CctvPeopleCountPanel, CctvRecentLogPanel, GridItemLayout } from '.';

const props = defineProps<{
    gridSettings: { columns: number; cellHeight: number };
    position: AdminCctvPositionLayout;
    active: boolean;
}>();

// Coalesce a burst of camera events into a single background refresh.
const REFRESH_DEBOUNCE_MS = 1000;

const gridRef = ref<HTMLElement>();
const gridInstance = ref<GridStack | null>(null);
const refreshTimer = ref<ReturnType<typeof setTimeout> | null>(null);

const {
    rooms,
    filteredEvents,
    filteredDwell,
    peopleCount,
    demographics,
    overallDwellAverage,
    lastUpdated,
    selectedLocation,
    selectedRange,
    isLoading,
    fetchCorsightData,
} = useCorsightMonitoring();

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
    if (props.active) {
        listenCorsight();
    }
});

onUnmounted(() => {
    stopCorsight();
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
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2 px-2 py-2">
            <span class="border-success/20 bg-success/10 text-success flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-bold">
                <span class="bg-success size-2 animate-pulse rounded-full" />
                {{ $t('page.admin_dashboard.cctv.label.live') }}
            </span>

            <div class="flex-1" />

            <span v-if="lastUpdated" class="text-secondary-muted-text hidden text-xs font-medium md:inline">
                {{ $t('page.admin_dashboard.cctv.label.last_updated', { time: dayjs(lastUpdated).format('MMM D, YYYY h:mm A') }) }}
            </span>

            <Select v-model="selectedLocation">
                <SelectTrigger class="focus-visible:border-ring focus-visible:ring-ring/20 w-40 cursor-pointer focus-visible:ring">
                    <SelectValue :value="selectedLocation" class="fluid-text-xs-sm" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all" class="fluid-text-xs-sm">
                        {{ $t('page.admin_dashboard.cctv.label.all_locations') }}
                    </SelectItem>
                    <SelectItem v-for="room in rooms" :key="room.name" :value="room.name" class="fluid-text-xs-sm">
                        {{ room.name }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="selectedRange">
                <SelectTrigger class="focus-visible:border-ring focus-visible:ring-ring/20 w-36 cursor-pointer focus-visible:ring">
                    <SelectValue :value="selectedRange" class="fluid-text-xs-sm" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="range in ['today', 'last_7_days', 'last_30_days']" :key="range" :value="range" class="fluid-text-xs-sm">
                        {{ $t(`page.admin_dashboard.cctv.filter.${range}`) }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- KPI strip -->
        <div class="px-2 pt-2">
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
