<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { energyCostData } from '@/routes/admin/electric-report';
import { ElectricMonitoringReportProps } from '@/types';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();

const cost = ref();
const breakdown = ref();
const latestRate = ref();

const chartOption = computed(() => ({
    type: 'line',
    tooltipConfig: {
        show: false,
    },
    yAxisConfig: {
        axisLabel: {
            formatter: '₱ {value}',
        },
    },
    xAxisConfig: {
        axisLabel: {
            padding: [10, 0, 0, 0],
        },
    },
}));

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.energy_cost) {
        cost.value = props.preloadedData.energy_cost.series;
        breakdown.value = props.preloadedData.energy_cost.monthly_breakdown;
        latestRate.value = props.preloadedData.energy_cost.latest_rate?.rate_per_kwh;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(energyCostData.url(), {
                params: {
                    ...props.filters,
                    jobId: props.jobId,
                },
            })
            .then((response) => {
                cost.value = response.data.series;
                breakdown.value = response.data.monthly_breakdown;
                latestRate.value = response.data.latest_rate?.rate_per_kwh;
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
        <h2 class="text-primary-text mb-4 text-xl font-bold">{{ $t('page.electric_monitoring_report.header.energy_cost') }}</h2>
        <span class="text-sm">{{ $t('page.electric_monitoring_report.description.energy_cost') }}</span>

        <!-- Current Rate Card -->
        <div class="border-b p-6">
            <div class="rounded-xl border border-blue-200/60 bg-gradient-to-r from-blue-50 to-indigo-50 p-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                        <Icon icon="pepicons-pop:electricity" class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-600">
                            {{ $t('page.electric_monitoring_report.label.current_effective_rate') }}
                        </p>
                        <p class="text-2xl font-bold">
                            ₱{{ Number(latestRate || 0).toFixed(2) }}
                            <span class="text-sm font-normal text-gray-500">/{{ $t('unit.kwh') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.monthly_breakdown') }}</h3>
                <span class="text-secondary-text text-xs">{{ breakdown?.length }} {{ $t('label.months') }}</span>
            </div>

            <div class="overflow-hidden rounded-sm border border-gray-200">
                <Table class="text-primary-text table-fixed text-sm">
                    <TableHeader>
                        <TableRow class="bg-table-header-background">
                            <TableHead class="py-2">{{ $t('table.header.month') }}</TableHead>
                            <TableHead class="py-2">{{ $t('table.header.total_cost') }}</TableHead>
                            <TableHead class="py-2">{{ $t('table.header.consumption') }}</TableHead>
                            <TableHead class="py-2">{{ $t('table.header.cost_kwh') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="data in breakdown" :key="data.month">
                            <TableCell class="py-2 font-medium">{{ data.month }}</TableCell>
                            <TableCell class="py-2 font-semibold"> ₱{{ data.total_cost.toLocaleString() }}</TableCell>
                            <TableCell class="py-2 font-medium">
                                {{ data.consumption.toLocaleString() }}
                                <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh') }}</span>
                            </TableCell>
                            <TableCell class="py-2 font-medium">₱{{ Number(data.rate).toFixed(2) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>

    <!-- Weekly Cost Trend Chart -->
    <div class="h-[1008px] p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.weekly_cost_trend') }}</h3>
        <div class="h-[450px] rounded-sm border bg-white pb-2">
            <LineChart :option="chartOption" :series="cost" />
        </div>
    </div>
</template>
