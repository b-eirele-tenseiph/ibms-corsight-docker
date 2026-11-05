<script setup lang="ts">
import {
    CarbonEmissionPanel,
    ConsumptionComparisonPanel,
    CostComparisonChart,
    HourlyConsumptionTrend,
    MonthlyConsumptionTrend,
    ReportHeader,
    SummaryCards,
    TotalConsumptionPerMeterPanel,
} from '@/components/admin/electric-monitoring-report';
import { ElectricMonitoringReportProps } from '@/types';

defineProps<ElectricMonitoringReportProps>();
</script>

<template>
    <div class="container mx-auto space-y-2 bg-white">
        <div class="h-[1008px]">
            <ReportHeader :meters :filters />

            <div class="mb-4 flex flex-col gap-4">
                <h2 class="text-primary-text text-xl font-bold">{{ $t('page.electric_monitoring_report.header.energy_overview') }}</h2>

                <SummaryCards :filters :preloadedData :jobId />
            </div>
            <div class="flex flex-col gap-4">
                <h2 class="text-primary-text text-xl font-bold">{{ $t('page.electric_monitoring_report.header.energy_breakdown') }}</h2>
                <span class="text-sm">{{ $t('page.electric_monitoring_report.description.energy_breakdown') }}</span>

                <TotalConsumptionPerMeterPanel :filters :preloadedData :jobId />
            </div>
        </div>

        <div class="h-[1008px]">
            <h2 class="text-primary-text mb-4 text-xl font-bold">{{ $t('page.electric_monitoring_report.header.energy_patterns') }}</h2>
            <span class="text-sm"> {{ $t('page.electric_monitoring_report.description.energy_patterns') }} </span>

            <div class="flex flex-col">
                <ConsumptionComparisonPanel :filters :preloadedData :jobId />

                <HourlyConsumptionTrend :filters :preloadedData :jobId />
            </div>
        </div>

        <div class="h-[1008px]">
            <div class="flex flex-col gap-4">
                <MonthlyConsumptionTrend :filters :preloadedData :jobId />
            </div>
        </div>

        <CostComparisonChart :filters :preloadedData :jobId />

        <CarbonEmissionPanel :filters :preloadedData :jobId />
    </div>
</template>
