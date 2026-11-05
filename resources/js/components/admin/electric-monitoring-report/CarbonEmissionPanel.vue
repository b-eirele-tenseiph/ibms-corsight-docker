<script setup lang="ts">
import { carbonFootprintData } from '@/routes/admin/electric-report';
import { CarbonMonthlyBreakdown, CarbonSummary, CarbonTrend, ElectricMonitoringReportProps } from '@/types';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, ref } from 'vue';
import { CarbonEmissionsBreakdown, CarbonEmissionsSummary, CarbonEmissionsTrendChart, ReportFooter } from '.';

const props = defineProps<ElectricMonitoringReportProps>();

const trend = ref<Array<CarbonTrend>>();
const summary = ref<CarbonSummary>();
const breakdown = ref<Array<CarbonMonthlyBreakdown>>();

const days = computed<number>(() => dayjs(props.filters.end).diff(props.filters.start, 'day') + 1);

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.carbon_footprint) {
        trend.value = props.preloadedData.carbon_footprint.trend;
        summary.value = props.preloadedData.carbon_footprint.summary;
        breakdown.value = props.preloadedData.carbon_footprint.monthly_breakdown;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(carbonFootprintData.url(), {
                params: {
                    ...props.filters,
                    jobId: props.jobId,
                },
            })
            .then((response) => {
                trend.value = response.data.trend;
                summary.value = response.data.summary;
                breakdown.value = response.data.monthly_breakdown;
            })
            .finally(() => {});
    });
};

onMounted(() => {
    loadConsumptionData();
});
</script>

<template>
    <div class="h-[1008px]">
        <h2 class="text-primary-text mb-4 text-xl font-bold">{{ $t('page.electric_monitoring_report.header.carbon_footprint') }}</h2>
        <span class="text-sm"> {{ $t('page.electric_monitoring_report.description.carbon_footprint') }}</span>
        <div class="flex flex-col">
            <CarbonEmissionsSummary :summary="summary" :days="days" />

            <CarbonEmissionsTrendChart :trend="trend ?? []" />
        </div>
    </div>

    <div class="flex h-[1008px] flex-col gap-4">
        <CarbonEmissionsBreakdown :breakdown="breakdown" />

        <ReportFooter />
    </div>
</template>
