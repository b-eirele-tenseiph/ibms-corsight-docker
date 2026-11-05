<script setup lang="ts">
import { BarChart } from '@/components/shared/charts';
import { monthlyConsumptionData } from '@/routes/admin/electric-report';
import { ElectricMonitoringReportProps } from '@/types';
import axios from 'axios';
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();
const data = ref();

const sortedData = computed(() => {
    if (!data.value) return null;

    return data.value.map((series: any) => ({
        ...series,
        data: [...series.data].sort((a: any, b: any) => a[0].localeCompare(b[0])),
    }));
});

const chartOption = computed(() => ({
    type: 'bar',
    tooltipConfig: {
        show: false,
    },
    yAxisConfig: {
        name: 'kWh',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    extras: {
        legend: {
            show: true,
            bottom: 0,
            type: 'scroll',
            orient: 'horizontal',
            data: sortedData.value?.map((item: any) => item.name),
        },
    },
}));

const loadConsumptionData = () => {
    if (props.preloadedData?.monthly_consumption) {
        data.value = props.preloadedData.monthly_consumption;
        return;
    }
    nextTick(() => {
        axios
            .get(monthlyConsumptionData.url(), {
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
        <h3 class="mb-4 text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.monthly_consumption') }}</h3>
        <div class="h-[450px] rounded-sm border bg-white pb-2">
            <BarChart :option="chartOption" :series="sortedData" />
        </div>
    </div>
</template>
