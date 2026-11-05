<script setup lang="ts">
import { AdminDashboardData, AdminEnergyPositionLayout } from '@/types';
import { GridStack } from 'gridstack';
import { onMounted, ref, watch } from 'vue';
import {
    CarbonTrackerPanel,
    ElectricMeterList,
    GridItemLayout,
    HourlyConsumptionPanel,
    MonthlyConsumptionPanel,
    MonthlyEnergyCostPanel,
    PowerFactorPanel,
} from '.';

const props = defineProps<{
    gridSettings: { columns: number; cellHeight: number };
    data: AdminDashboardData;
    position: AdminEnergyPositionLayout;
}>();

const gridRef = ref<HTMLElement>();
const gridInstance = ref<GridStack | null>(null);

const initializeGrid = () => {
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
};

onMounted(() => {
    initializeGrid();
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
                    x: position.hourlyConsumptionPosition.x,
                    y: position.hourlyConsumptionPosition.y,
                    w: position.hourlyConsumptionPosition.w,
                    h: position.hourlyConsumptionPosition.h,
                }"
            >
                <HourlyConsumptionPanel :meter="data.electricMeter" />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.monthlyConsumptionPosition.x,
                    y: position.monthlyConsumptionPosition.y,
                    w: position.monthlyConsumptionPosition.w,
                    h: position.monthlyConsumptionPosition.h,
                }"
            >
                <MonthlyConsumptionPanel />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.carbonTrackerPosition.x,
                    y: position.carbonTrackerPosition.y,
                    w: position.carbonTrackerPosition.w,
                    h: position.carbonTrackerPosition.h,
                }"
            >
                <CarbonTrackerPanel :meter="data.electricMeter" />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.powerFactorPosition.x,
                    y: position.powerFactorPosition.y,
                    w: position.powerFactorPosition.w,
                    h: position.powerFactorPosition.h,
                }"
            >
                <PowerFactorPanel :meter="data.electricMeter" />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.dailyEnergyCostPosition.x,
                    y: position.dailyEnergyCostPosition.y,
                    w: position.dailyEnergyCostPosition.w,
                    h: position.dailyEnergyCostPosition.h,
                }"
            >
                <MonthlyEnergyCostPanel />
            </GridItemLayout>
            <GridItemLayout
                :position="{
                    x: position.electricMeterPosition.x,
                    y: position.electricMeterPosition.y,
                    w: position.electricMeterPosition.w,
                    h: position.electricMeterPosition.h,
                }"
            >
                <ElectricMeterList :location="data.location_with_meters ?? []" />
            </GridItemLayout>
        </div>
    </div>
</template>
