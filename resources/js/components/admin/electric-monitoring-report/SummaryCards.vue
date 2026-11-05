<script setup lang="ts">
import { summaryData } from '@/routes/admin/electric-report';
import { ElectricMonitoringReportProps, EnergySummary } from '@/types';
import axios from 'axios';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();

const data = ref<EnergySummary>({
    total_consumption: 0,
    total_cost: 0,
    carbon_footprint: 0,
});

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.summary) {
        data.value = props.preloadedData.summary;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(summaryData.url(), {
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
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-accent/50 rounded-lg border p-5">
            <div class="mb-2 flex items-center gap-2">
                <p class="text-secondary-text text-sm font-medium">{{ $t('page.electric_monitoring_report.label.total_consumption') }}</p>
            </div>
            <p class="text-xl font-bold">
                {{ data.total_consumption.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} {{ $t('unit.kwh') }}
            </p>
        </div>

        <div class="bg-accent/50 rounded-lg border p-5">
            <div class="mb-2 flex items-center gap-2">
                <p class="text-secondary-text text-sm font-medium">{{ $t('page.electric_monitoring_report.label.total_cost') }}</p>
            </div>
            <p class="text-xl font-bold">₱{{ data.total_cost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
        </div>

        <div class="bg-accent/50 rounded-lg border p-5">
            <div class="mb-2 flex items-center gap-2">
                <p class="text-secondary-text text-sm font-medium">{{ $t('page.electric_monitoring_report.label.carbon_footprint') }}</p>
            </div>
            <p class="text-xl font-bold">
                {{ data.carbon_footprint.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                {{ $t('unit.metric_ton') }}
            </p>
        </div>
    </div>
</template>
