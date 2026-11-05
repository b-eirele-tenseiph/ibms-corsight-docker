<script setup lang="ts">
import { BarChart } from '@/components/shared/charts';
import { StateEmpty } from '@/components/shared/states';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TabsContent } from '@/components/ui/tabs';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { Icon } from '@iconify/vue';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const { isExtraSmallMobile } = useBreakpoints();

const monthlyBilling = [
    {
        name: 'All Water Meters',
        data: [
            ['2025-03', '3146.00'],
            ['2025-04', '3381.00'],
            ['2025-05', '3308.00'],
            ['2025-06', '3383.00'],
            ['2025-07', '3521.50'],
            ['2025-08', '3274.80'],
            ['2025-09', '3612.20'],
            ['2025-10', '3448.90'],
            ['2025-11', '3589.40'],
            ['2025-12', '3734.60'],
            ['2026-01', '3812.30'],
            ['2026-02', '3965.70'],
        ],
    },
];

const history = ref([
    { month: 'February 2026', consumption: 2450, cost: 3965.7, avg: 49 },
    { month: 'January 2026', consumption: 2380, cost: 3812.3, avg: 48 },
    { month: 'December 2025', consumption: 2680, cost: 3734.6, avg: 54 },
    { month: 'November 2025', consumption: 2320, cost: 3589.4, avg: 46 },
    { month: 'October 2025', consumption: 2180, cost: 3448.9, avg: 44 },
    { month: 'September 2025', consumption: 2410, cost: 3612.2, avg: 48 },
    { month: 'August 2025', consumption: 2290, cost: 3274.8, avg: 46 },
    { month: 'July 2025', consumption: 2150, cost: 3521.5, avg: 43 },
    { month: 'June 2025', consumption: 2340, cost: 3383.0, avg: 47 },
    { month: 'May 2025', consumption: 2260, cost: 3308.0, avg: 45 },
    { month: 'April 2025', consumption: 2190, cost: 3381.0, avg: 44 },
    { month: 'March 2025', consumption: 2070, cost: 3146.0, avg: 41 },
]);

const avgMonthlyRevenue = computed(() => {
    const total = history.value.reduce((sum, m) => sum + m.cost, 0);
    return Math.round(total / history.value.length);
});

const avgMonthlyConsumption = computed(() => {
    const total = history.value.reduce((sum, m) => sum + m.consumption, 0);
    return Math.round(total / history.value.length);
});

const totalConsumption = computed(() => history.value.reduce((sum, m) => sum + m.consumption, 0));

const totalCost = computed(() => history.value.reduce((sum, m) => sum + m.cost, 0));

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return history.value.length > 0 || isSmallScreen.value ? history.value : [{}];
});
</script>

<template>
    <TabsContent value="chart">
        <div class="flex h-full flex-col gap-2">
            <div class="min-h-0 flex-1">
                <BarChart :series="monthlyBilling" />
            </div>

            <div class="grid gap-2 min-[420px]:grid-cols-2">
                <div class="bg-info-bg border-info-border text-info-text rounded-lg border p-4">
                    <p class="fluid-text-xs-sm mb-1 font-medium">{{ $t('page.water_billing_report.label.avg_monthly_cost') }}</p>
                    <p class="fluid-text-sm-lg font-bold">₱{{ avgMonthlyRevenue.toLocaleString('en-PH') }}</p>
                    <p class="mt-1 flex items-center gap-1 text-xs">
                        <Icon icon="lucide:trending-up" />
                        {{ $t('page.water_billing_report.label.vs_last_period', { percentage: '5.6' }) }}
                    </p>
                </div>
                <div class="bg-success-bg border-success-border text-success-text rounded-lg border p-4">
                    <p class="fluid-text-xs-sm mb-1 font-medium">{{ $t('page.water_billing_report.label.avg_consumption') }}</p>
                    <p class="fluid-text-sm-lg font-bold">{{ avgMonthlyConsumption.toLocaleString('en-PH') }} m³</p>
                    <p class="mt-1 flex items-center gap-1 text-xs">
                        <Icon icon="lucide:trending-up" />
                        {{ $t('page.water_billing_report.label.vs_last_period', { percentage: '2.8' }) }}
                    </p>
                </div>
            </div>
        </div>
    </TabsContent>

    <TabsContent value="table">
        <div class="h-74">
            <div class="h-full overflow-hidden p-2">
                <ScrollArea class="h-full">
                    <Table class="table-main table-fixed">
                        <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                            <template v-for="(acc, index) in tableData" :key="index">
                                <TableRow class="table-header-row [&_th]:h-12 [&_th]:truncate [&_th]:py-3">
                                    <TableHead>{{ $t('table.header.month') }}</TableHead>
                                    <TableHead>{{ $t('table.header.usage') }}</TableHead>
                                    <TableHead>{{ $t('table.header.charges') }}</TableHead>
                                    <TableHead>{{ $t('table.header.avg_meter') }}</TableHead>
                                </TableRow>
                            </template>
                        </TableHeader>
                        <TableBody
                            v-if="history.length > 0"
                            class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                        >
                            <TableRow v-for="month in history" :key="month.month" class="table-body-row tabular-nums [&_td]:h-12 [&_td]:py-3">
                                <TableCell>{{ month.month }}</TableCell>
                                <TableCell class="tabular-nums">{{ month.consumption.toLocaleString('en-PH') }} m³</TableCell>
                                <TableCell class="font-semibold tabular-nums">
                                    ₱{{ month.cost.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                                </TableCell>
                                <TableCell class="tabular-nums">
                                    {{ month.avg }} <span class="text-secondary-text text-xs font-medium">m³</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                        <TableBody v-else>
                            <TableEmpty :colspan="4">
                                <StateEmpty />
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </ScrollArea>
            </div>
        </div>

        <div class="bg-info-bg border-info-border mt-4 rounded-lg border p-4">
            <div class="flex items-start gap-2">
                <div class="text-info-text fluid-text-xs-sm space-y-2">
                    <Label class="font-semibold">{{
                        $t('page.water_billing_report.label.6_month_summary', {
                            count: history.length,
                        })
                    }}</Label>

                    <ul class="list-inside list-disc space-y-1">
                        <li>
                            {{ $t('page.water_billing_report.label.total_usage') }}:
                            <span class="font-semibold">{{ totalConsumption.toLocaleString('en-PH') }} m³</span>
                        </li>
                        <li>
                            {{ $t('page.water_billing_report.label.total_cost') }}:
                            <span class="font-semibold"> ₱{{ totalCost.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }} </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TabsContent>
</template>
