<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { useMediaQuery } from '@vueuse/core';
import dayjs from 'dayjs';
import { computed, ref } from 'vue';
import { ReportMonthlyBillingSummaryFilter } from '.';

const { isExtraSmallMobile } = useBreakpoints();

const props = defineProps<{
    billing_dates: { date: string }[];
    billingSummary: {
        id: number;
        name: string;
        consumer_type: string;
        billing_date: string;
        meter_size: string;
        usage: number;
        amount: number;
    }[];
}>();

const consumerBadgeColor = (type: string) => {
    const colors = {
        residential: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800',
        commercial: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800',
        industrial: 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950 dark:text-violet-300 dark:border-violet-800',
    };
    return colors[type as keyof typeof colors] || colors.residential;
};

const searchQuery = ref('');
const selectedDate = ref(props.billing_dates[0]?.date ?? null);
const selectedType = ref('');
const selectedMeter = ref('');

const formatDate = (date: string) => dayjs(date).format('MMMM YYYY');

const columns = ['name', 'consumer_type', 'meter_size', 'previous', 'usage', 'amount'];

const filteredSummary = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    const type = selectedType.value;
    const meter = selectedMeter.value;
    const date = selectedDate.value;

    return props.billingSummary.filter((item: any) => {
        const matchesDate = !date || item.billing_date === date;
        const matchesSearch = !q || columns.some((col) => String(item[col]).toLowerCase().includes(q));
        const matchesType = !type || type === 'all' || item.consumer_type.toLowerCase() === type.toLowerCase();
        const matchesMeter = !meter || meter === 'all' || item.meter_size.toLowerCase() === meter.toLowerCase();

        return matchesDate && matchesSearch && matchesType && matchesMeter;
    });
});

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return filteredSummary.value.length > 0 || isSmallScreen.value ? filteredSummary.value : [{}];
});

const totalConsumption = computed(() => {
    return filteredSummary.value.reduce((sum, item) => sum + item.usage, 0);
});

const totalCost = computed(() => {
    return filteredSummary.value.reduce((sum, item) => sum + item.amount, 0);
});

const averageUsage = computed(() => {
    return totalConsumption.value / filteredSummary.value.length;
});
</script>

<template>
    <Card class="h-full gap-4 py-4">
        <CardHeader>
            <div class="flex flex-col justify-between space-y-2 sm:flex-row">
                <div class="flex w-full flex-col">
                    <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.water_billing_report.header.billing_summary') }}</span>
                    <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{
                        $t('page.water_billing_report.header.billing_summary_subtitle')
                    }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <div class="min-w-40">
                        <SelectArea
                            v-model="selectedDate"
                            :items="billing_dates.map((b) => ({ date: b.date, label: formatDate(b.date) }))"
                            itemKey="label"
                            valueKey="date"
                        />
                    </div>
                    <!-- <Button variant="outline">
                        <Icon icon="lets-icons:export" class="size-4" />
                        <span class="hidden md:flex">{{ $t('operation.export') }}</span>
                    </Button> -->
                </div>
            </div>
        </CardHeader>
        <CardContent>
            <div class="xs:grid-cols-3 mb-6 grid gap-2">
                <div class="border-info-border bg-info-bg text-info-text rounded-lg border p-4">
                    <p class="fluid-text-xs-sm truncate font-medium">{{ $t('page.water_billing_report.label.total_usage') }}</p>
                    <p class="fluid-text-base-xl font-bold">{{ totalConsumption.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }} m³</p>
                    <p class="text-xs">{{ $t('page.water_billing_report.label.total_usage_description') }}</p>
                </div>
                <div class="border-success-border bg-success-bg text-success-text rounded-lg border p-4">
                    <p class="fluid-text-xs-sm truncate font-medium">{{ $t('page.water_billing_report.label.total_cost') }}</p>
                    <p class="fluid-text-base-xl font-bold">₱{{ totalCost.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</p>
                    <p class="text-xs">{{ $t('page.water_billing_report.label.total_cost_description') }}</p>
                </div>
                <div class="border-warning-border bg-warning-bg text-warning-text rounded-lg border p-4">
                    <p class="fluid-text-xs-sm truncate font-medium">{{ $t('page.water_billing_report.label.average_usage') }}</p>
                    <p class="fluid-text-base-xl font-bold">{{ averageUsage.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }} m³</p>
                    <p class="text-xs">{{ $t('page.water_billing_report.label.average_usage_description') }}</p>
                </div>
            </div>

            <div class="mb-4 flex gap-3">
                <InputSearch v-model="searchQuery" />
                <ReportMonthlyBillingSummaryFilter v-model:selectedType="selectedType" v-model:selectedMeter="selectedMeter" />
            </div>

            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(acc, index) in tableData" :key="index">
                        <TableRow class="table-header-row [&_th]:h-12 [&_th]:truncate [&_th]:py-3">
                            <TableHead>{{ $t('table.header.meter') }}</TableHead>
                            <TableHead>{{ $t('table.header.consumer_type') }}</TableHead>
                            <TableHead>{{ $t('table.header.usage') }}</TableHead>
                            <TableHead>{{ $t('table.header.amount') }}</TableHead>
                        </TableRow>
                    </template>
                </TableHeader>
                <TableBody
                    v-if="filteredSummary.length > 0"
                    class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                >
                    <TableRow v-for="meter in filteredSummary" :key="meter.id" class="table-body-row tabular-nums [&_td]:h-12 [&_td]:py-3">
                        <TableCell>{{ meter.name }}</TableCell>
                        <TableCell>
                            <div class="flex flex-row items-center gap-2 lg:flex-col lg:items-start">
                                <Badge :class="consumerBadgeColor(meter.consumer_type)" class="text-xs">
                                    {{ meter.consumer_type }}
                                </Badge>
                                <div class="text-secondary-text text-xs">
                                    <span class="font-medium">Size:</span>
                                    <span class="text-primary-text ml-1 font-semibold tabular-nums">{{ meter.meter_size }}" </span>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell
                            >{{ meter.usage.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                            <span class="text-secondary-text text-xs font-medium">m³</span>
                        </TableCell>

                        <TableCell class="font-bold">₱{{ meter.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</TableCell>
                    </TableRow>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="4">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
