<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { CarbonMonthlyBreakdown } from '@/types';
import { Icon } from '@iconify/vue';

withDefaults(
    defineProps<{
        breakdown?: Array<CarbonMonthlyBreakdown>;
    }>(),
    {
        breakdown: (): Array<CarbonMonthlyBreakdown> => [
            {
                month: '',
                consumption: 0,
                co2_emissions: 0,
                excess_emissions: 0,
                potential_savings: 0,
                daily_average: 0,
            },
        ],
    },
);
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex flex-row items-center justify-between border-b pb-6">
            <h3 class="flex-shrink-0 text-lg font-semibold">
                {{ $t('page.electric_monitoring_report.header.carbon_footprint_breakdown') }}
            </h3>
            <div class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700">
                <Icon icon="ri:target-line" class="size-4" />
                <span>{{
                    $t('unit.target_kwh_month', {
                        target: '10,000',
                    })
                }}</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-sm border">
            <Table class="text-primary-text text-sm">
                <TableHeader>
                    <TableRow class="bg-table-header-background">
                        <TableHead class="py-2">{{ $t('table.header.month') }}</TableHead>
                        <TableHead class="py-2">{{ $t('table.header.consumption') }}</TableHead>
                        <TableHead class="py-2">{{ $t('table.header.co2_emissions') }} </TableHead>
                        <TableHead class="w[15%] py-2">{{ $t('table.header.excess_emissions') }}</TableHead>
                        <TableHead class="w[15%] py-2">{{ $t('table.header.potential_savings') }}</TableHead>
                        <TableHead class="py-2">{{ $t('table.header.daily_average') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="data in breakdown" :key="data.month">
                        <TableCell class="py-2 font-medium">{{ data.month }}</TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.consumption.toLocaleString() }} <span class="text-secondary-muted-text text-xs">{{ $t('unit.kwh') }}</span>
                        </TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.co2_emissions.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kg') }}</span>
                        </TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.excess_emissions.toLocaleString() }}
                            <span class="text-secondary-muted-text text-xs">{{ $t('unit.kg') }}</span>
                        </TableCell>
                        <TableCell class="py-2 font-medium">₱{{ data.potential_savings.toLocaleString() }} </TableCell>
                        <TableCell class="py-2 font-medium">
                            {{ data.daily_average.toLocaleString() }} <span class="text-secondary-muted-text text-xs">{{ $t('unit.kg_day') }}</span>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
