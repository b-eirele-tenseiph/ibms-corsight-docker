<script setup lang="ts">
import { PieChart } from '@/components/shared/charts';
import { totalConsumptionData } from '@/routes/admin/electric-report';
import { ElectricMonitoringReportProps } from '@/types';
import axios from 'axios';
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();
const data = ref();

const chartOption = computed(() => ({
    legend: {
        show: false,
    },
    seriesConfig: {
        label: {
            show: true,
            overflow: 'break',
            width: 250,
            position: 'outside',
            formatter: (params: any) => {
                return `${params.name}: ${params.value.toLocaleString()} kWH (${params.percent}%)`;
            },
        },
        labelLine: {
            show: true,
            length: 15,
            length2: 10,
        },
    },
}));

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.total_consumption) {
        data.value = props.preloadedData.total_consumption;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(totalConsumptionData.url(), {
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
    <div class="h-[400px]">
        <PieChart :series="data" :option="chartOption" />
    </div>
</template>
