<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { consumptionData } from '@/routes/admin/electric-report';
import { ConsumptionComparison, ElectricMonitoringReportProps } from '@/types';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps<ElectricMonitoringReportProps>();

const data = ref<ConsumptionComparison>();

const days = computed<number>(() => dayjs(props.filters.end).diff(props.filters.start, 'day') + 1);

const loadConsumptionData = () => {
    // Use preloaded data if available (for PDF rendering)
    if (props.preloadedData?.consumption_report) {
        data.value = props.preloadedData.consumption_report;
        return;
    }

    // Otherwise, fetch data via axios
    nextTick(() => {
        axios
            .get(consumptionData.url(), {
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
        <h3 class="mb-4 text-lg font-semibold">
            {{ $t('page.electric_monitoring_report.header.current_consumption_metric') }}
        </h3>

        <div class="overflow-hidden rounded-sm border">
            <Table class="text-primary-text table-fixed text-sm">
                <TableHeader>
                    <TableRow class="bg-table-header-background">
                        <TableHead class="w-[25%] py-2">{{ $t('table.header.metric') }}</TableHead>
                        <TableHead class="w-[25%] py-2">{{ $t('table.header.current') }}</TableHead>
                        <TableHead class="w-[35%] py-2">
                            <div class="flex flex-col">
                                {{ $t('table.header.previous') }}
                                <span class="text-secondary-text text-xs">{{
                                    $t('page.electric_monitoring_report.label.last_days_from_end', { days: days })
                                }}</span>
                            </div>
                        </TableHead>
                        <TableHead class="w-[15%] py-2">{{ $t('table.header.change') }}</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody v-if="data">
                    <TableRow>
                        <TableCell class="py-2 font-semibold">{{ $t('table.body.total_consumption') }}</TableCell>
                        <TableCell class="py-2 font-semibold">
                            {{ data.consumption.current.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh') }}</span>
                        </TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.consumption.prev.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh') }}</span>
                        </TableCell>
                        <TableCell :class="['py-2 font-semibold', (data.consumption.changes ?? 0) < 0 ? 'text-error' : 'text-success']">
                            {{ (data.consumption.changes ?? 0).toLocaleString() }}%
                        </TableCell>
                    </TableRow>

                    <TableRow>
                        <TableCell class="py-2 font-semibold">{{ $t('table.body.average_daily_usage') }}</TableCell>
                        <TableCell class="py-2 font-semibold">
                            {{ data.avg_daily.current.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh_day') }} </span>
                        </TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.avg_daily.prev.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh') }}</span>
                        </TableCell>
                        <TableCell :class="['py-2 font-semibold', (data.avg_daily.changes ?? 0) < 0 ? 'text-error' : 'text-success']">
                            {{ (data.avg_daily.changes ?? 0).toLocaleString() }}%
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
