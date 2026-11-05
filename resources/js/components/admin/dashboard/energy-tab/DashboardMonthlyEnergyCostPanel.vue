<script setup lang="ts">
import { BarChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { useLocalStorageRef } from '@/composables/useLocalStorage';
import { useTooltipContentColor } from '@/lib/utils';
import { dailyEnergyData } from '@/routes/admin/dashboard';
import { rateIndex } from '@/routes/admin/management/settings/system';
import { ChartSeries, CostData, DateOption } from '@/types';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { MonthlyEnergyCostFilter, MonthlyEnergyCostTooltip } from '..';

const monthlyEnergy = ref<Array<any>>([]);
const totalCost = ref<CostData>({});
const isLoading = ref<boolean>(false);
const { tooltipStyles } = useTooltipContentColor();

const months: Pick<DateOption, 'label'>[] = [{ label: 'current_month' }, { label: 'last_month' }];
const selectedMonth = useLocalStorageRef<string>('selectedEnergyMonth', 'current_month');

const formatCost = (val: any): string => (Number(val) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
const formatKwh = (val: any): string => (Number(val) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const chartSeries = computed<ChartSeries[]>(() =>
    // Only pass x and y value
    monthlyEnergy.value.map((s) => ({
        name: s.name,
        data: s.data.map((d: any) => [d[0], d[1]]),
    })),
);

const noRateSet = computed(() => {
    const current = totalCost.value['current_month'];
    return current?.kwh && current.kwh > 0 && !current?.hasRate;
});

const chartOption = computed(() => ({
    xAxisConfig: {
        axisLabel: {
            formatter: (value: any) => dayjs(value).format('MMM D'),
        },
    },
    yAxisConfig: {
        axisLabel: {
            formatter: (value: any) => '₱' + value,
        },
    },
    tooltipConfig: {
        formatter: (params: any) => {
            const p = Array.isArray(params) ? params[0] : params;
            const raw = monthlyEnergy.value[0]?.data[p.dataIndex] || [];
            const hasGap = raw[2];
            const kwh = raw[3];
            // Short way: default to 0 if value is missing/dash
            const cost = formatCost(p.value[1] || 0);

            let html = `<div style="font-weight:600; font-size:12px;">${dayjs(p.axisValue).format('MMM D, YYYY')}</div>
                    <div style="font-size:12px; margin-top:4px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            ${p.marker} Cost: <span style="font-weight:600;">₱ ${cost}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; margin-left:18px;">
                            kWh: <span style="font-weight:600;">${formatKwh(kwh)}</span>
                        </div>
                    </div>`;

            const s = tooltipStyles.value;

            if (hasGap) {
                html += `<div style="margin-top:8px; padding:8px; background:${s.bg}; border-radius:4px; font-size:11px; color:${s.text};">
                <strong style="color:${s.strong};">Incomplete Data</strong><br/>
                <em style="color:${s.text};">Meter gap detected during this period</em>
             </div>`;
            }
            return html;
        },
    },
    extras: {
        toolbox: {
            show: true,
            itemSize: 12,
            feature: {
                dataZoom: { yAxisIndex: 'none' },
                magicType: { type: ['line', 'bar'] },
                saveAsImage: {},
            },
        },
    },
}));

const loadConsumptionData = () => {
    nextTick(() => {
        isLoading.value = true;
        axios
            .get(dailyEnergyData.url(), {
                params: { month: selectedMonth.value },
            })
            .then((response) => {
                monthlyEnergy.value = response.data.daily_cost;
                totalCost.value = response.data.cost_total;
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

onMounted(() => loadConsumptionData());

watch(selectedMonth, () => loadConsumptionData());
</script>

<template>
    <Card class="h-full gap-4 p-4">
        <div class="flex w-full flex-row items-center gap-2">
            <div class="flex w-full flex-col">
                <span class="fluid-text-sm-base text-primary-text font-bold">
                    {{ $t('page.admin_dashboard.header.monthly_cost') }}
                </span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">
                    {{ $t('page.admin_dashboard.header.monthly_cost_subtitle') }} -
                    {{ dayjs().format('MMMM YYYY') }}
                </span>
            </div>
            <MonthlyEnergyCostFilter :months="months" v-model="selectedMonth" />
        </div>

        <!-- Cost Summary -->
        <div class="divide-border grid grid-cols-2 divide-x">
            <div v-for="period in ['last_month', 'current_month'] as const" :key="period" class="flex flex-col items-center gap-1">
                <template v-if="isLoading">
                    <div class="border-primary size-8 animate-spin rounded-full border-2 border-t-transparent"></div>
                </template>
                <template v-else>
                    <div class="relative">
                        <span class="text-muted-foreground text-sm font-medium">
                            {{ $t(`date_range_filter.${period}`) }}
                        </span>
                        <MonthlyEnergyCostTooltip
                            v-if="(totalCost[period]?.unattributedCost ?? 0) > 0"
                            :content="
                                $t('tooltip.unattributed_cost_message', {
                                    cost: formatCost(totalCost[period]?.unattributedCost),
                                })
                            "
                        />
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-muted-foreground text-xs font-medium">₱</span>
                        <span class="text-foreground fluid-text-sm-lg font-bold tracking-tight tabular-nums">
                            {{ formatCost(totalCost[period]?.cost) }}
                        </span>
                    </div>
                    <div class="text-muted-foreground text-xs font-medium tabular-nums">{{ formatKwh(totalCost[period]?.kwh) }} kWh</div>
                </template>
            </div>
        </div>

        <div class="relative grow overflow-hidden">
            <BarChart :option="chartOption" :loading="isLoading" :series="chartSeries" />

            <!-- No rate overlay -->
            <div v-if="!isLoading && noRateSet" class="bg-background/70 absolute inset-0 z-10 flex items-center justify-center">
                <div class="max-w-md space-y-3 px-5 text-center">
                    <Icon icon="lucide:circle-alert" class="text-warning mx-auto size-8" />
                    <div class="space-y-1">
                        <p class="text-secondary-text fluid-text-sm-base font-semibold">
                            {{ $t('page.admin_dashboard.energy.no_rate_title') }}
                        </p>
                        <p class="text-secondary-text fluid-text-xs-sm">
                            {{ $t('page.admin_dashboard.energy.no_rate_message') }}
                        </p>
                    </div>
                    <Link
                        :href="rateIndex()"
                        class="bg-primary text-primary-foreground hover:bg-primary/80 fluid-text-xs-sm rounded-md px-4 py-2 font-medium"
                    >
                        {{ $t('page.admin_dashboard.energy.set_rate_button') }}
                    </Link>
                </div>
            </div>
        </div>
    </Card>
</template>
