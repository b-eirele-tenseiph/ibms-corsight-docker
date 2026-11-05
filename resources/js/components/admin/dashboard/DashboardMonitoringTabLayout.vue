<script setup lang="ts">
import { AdminDashboardData, AdminMonitoringPositionLayout } from '@/types';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack-all.js';
import 'gridstack/dist/gridstack.min.css';
import { onMounted, ref, watch } from 'vue';
import {
    DeviceCountPanel,
    DeviceStatusPanel,
    DeviceSummaryPanel,
    ElectricityConsumptionPanel,
    EnvironmentalSensorPanel,
    GridItemLayout,
    PeopleCountMonitoringPanel,
    RecentActivityPanel,
    RoomMonitoringPanel,
} from '.';

const props = defineProps<{
    gridSettings: { columns: number; cellHeight: number };
    data: AdminDashboardData;
    position: AdminMonitoringPositionLayout;
}>();

const gridRef = ref<HTMLElement>();
const gridInstance = ref<GridStack | null>(null);

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
});

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
        <div ref="gridRef" class="grid-stack">
            <GridItemLayout
                :position="{
                    x: position.electricityCostPosition.x,
                    y: position.electricityCostPosition.y,
                    w: position.electricityCostPosition.w,
                    h: position.electricityCostPosition.h,
                }"
            >
                <ElectricityConsumptionPanel />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.deviceCountPosition.x,
                    y: position.deviceCountPosition.y,
                    w: position.deviceCountPosition.w,
                    h: position.deviceCountPosition.h,
                }"
            >
                <DeviceCountPanel />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.deviceActivePosition.x,
                    y: position.deviceActivePosition.y,
                    w: position.deviceActivePosition.w,
                    h: position.deviceActivePosition.h,
                }"
            >
                <DeviceSummaryPanel />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.roomPosition.x,
                    y: position.roomPosition.y,
                    w: position.roomPosition.w,
                    h: position.roomPosition.h,
                }"
            >
                <RoomMonitoringPanel :roomData="data.rooms" />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.deviceStatusPosition.x,
                    y: position.deviceStatusPosition.y,
                    w: position.deviceStatusPosition.w,
                    h: position.deviceStatusPosition.h,
                }"
            >
                <DeviceStatusPanel />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.recentActivityPosition.x,
                    y: position.recentActivityPosition.y,
                    w: position.recentActivityPosition.w,
                    h: position.recentActivityPosition.h,
                }"
            >
                <RecentActivityPanel />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.environmentDataPosition.x,
                    y: position.environmentDataPosition.y,
                    w: position.environmentDataPosition.w,
                    h: position.environmentDataPosition.h,
                }"
            >
                <EnvironmentalSensorPanel />
            </GridItemLayout>

            <GridItemLayout
                :position="{
                    x: position.peopleCountPosition.x,
                    y: position.peopleCountPosition.y,
                    w: position.peopleCountPosition.w,
                    h: position.peopleCountPosition.h,
                }"
            >
                <PeopleCountMonitoringPanel />
            </GridItemLayout>
        </div>
    </div>
</template>
