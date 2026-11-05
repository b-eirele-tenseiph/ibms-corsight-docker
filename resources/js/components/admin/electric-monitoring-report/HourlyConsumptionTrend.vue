<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { hourlyConsumptionData } from '@/routes/admin/electric-report';
import { ElectricMonitoringReportProps } from '@/types';
import axios from 'axios';
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();

const data = ref();

const chartOption = computed(() => ({
    type: 'line',
    tooltipConfig: {
        show: false,
    },
    xAxisConfig: {
        type: 'time',
        boundaryGap: false,
    },
    yAxisConfig: {
        name: 'kWh',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    seriesConfig: {
        showSymbol: false,
    },
}));

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.hourly_consumption) {
        data.value = props.preloadedData.hourly_consumption;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(hourlyConsumptionData.url(), {
                params: {
                    ...props.filters,
                    jobId: props.jobId,
                },
            })
            .then((response) => {
                data.value = response.data;
            })
            .finally(() => {});
    });
};

onMounted(() => {
    loadConsumptionData();
});
</script>

<template>
    <div class="p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.hourly_energy_trend') }}</h3>
        <div class="h-[450px] rounded-sm border bg-white pb-2">
            <LineChart :option="chartOption" :series="data" />
        </div>
    </div>
</template>
