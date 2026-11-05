<script setup lang="ts">
import { BarChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { useAppearance } from '@/composables/useAppearance';
import { useLocalStorageRef } from '@/composables/useLocalStorage';
import { monthlyEnergyData } from '@/routes/admin/dashboard';
import { ChartSeries, DateOption, MonthlyEnergyData } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import minMax from 'dayjs/plugin/minMax';
import utc from 'dayjs/plugin/utc';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { MonthlyConsumptionFilter } from '..';

dayjs.extend(minMax);
dayjs.extend(utc);

const monthlyConsumption = ref<Array<MonthlyEnergyData>>([]);
const isLoading = ref<boolean>(false);
const { appearance } = useAppearance();

const { t } = useI18n();

const months: DateOption[] = [
    { value: '3', label: '3_month' },
    { value: '6', label: '6_month' },
    { value: '12', label: '1_year' },
];

const chartSeries = computed<ChartSeries[]>(() =>
    monthlyConsumption.value.map((s) => ({
        name: s.name,
        data: s.data.map((d) => [d.month, d.consumption ?? 0] as [string, number]),
    })),
);

// Get the selected month (stored in localStorage)
const selectedMonth = useLocalStorageRef<string>('selectedMonth', '6');

// Compute latest updated time from consumption data
const latestUpdated = computed(() => {
    const validDates = monthlyConsumption.value.filter((item) => item.latest_data).map((item) => dayjs.utc(item.latest_data).add(8, 'hour'));

    const maxDate = dayjs.max(validDates);
    return maxDate ? maxDate.format('MMM DD YYYY h:mm:ss A') : 'N/A';
});

// Checker if latest update is within the last 30 minutes
const isRecentUpdate = (latestUpdated: any) => {
    if (!latestUpdated) return false;
    const now = dayjs();
    const updatedAt = dayjs(latestUpdated);
    return now.diff(updatedAt, 'minute') <= 30;
};

// Load consumption data from backend
const loadConsumptionData = () => {
    nextTick(() => {
        isLoading.value = true;

        axios
            .get(monthlyEnergyData.url(), { params: { month: selectedMonth.value } })
            .then((response) => {
                monthlyConsumption.value = response.data.monthly_consumption;
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

const chartOption = computed(() => ({
    yAxisConfig: {
        name: 'kWh',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    tooltipConfig: {
        formatter: function (params: any) {
            const fmt = (v: any) =>
                v != null ? Number(v).toFixed(2) + ' kWh' : `<span style="font-style: italic; font-weight: 400">${t('state.no_data')}</span>`;
            const month = params[0].axisValue;
            let html = `<div style="font-weight:600; font-size:12px;">${month}</div>`;

            params.forEach((p: any) => {
                const floor = monthlyConsumption.value[p.seriesIndex as number];
                const d = floor?.data[p.dataIndex as number];

                if (!d) return;

                const isDark = appearance.value == 'dark';

                html += `
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                        ${p.marker}
                        <span>${p.seriesName}:</span>
                        <span style="font-weight: 700;">${fmt(d.consumption)}</span>
                    </div>
                    <div style="font-size:12px; color:${isDark ? '#f5f5f5' : '#434343'}">
                        Confirmed: <span style="font-weight:600;">${fmt(d.confirmed_consumption)}</span>
                    </div>
                    <div style="font-size:12px; color:${isDark ? '#f5f5f5' : '#434343'}">
                        Unattributed: <span style="font-weight:600;">${fmt(d.unattributed_consumption)}</span>
                    </div>
                </div>
            `;
            });

            return html;
        },
    },
    extras: {
        legend: {
            show: true,
            bottom: 0,
            type: 'scroll',
            orient: 'horizontal',
            data: monthlyConsumption.value?.map((item) => item.name),
        },
        toolbox: {
            show: true,
            itemSize: 12,
            feature: {
                dataZoom: {
                    yAxisIndex: 'none',
                },
                magicType: {
                    type: ['line', 'bar', 'stack'],
                },
                saveAsImage: {},
            },
        },
    },
}));

const { stopListening: stopEnergy, listen: listenEnergy } = useEcho('energy_consumption', '.electric_meter.monthly_data', (payload: any) => {
    const series = monthlyConsumption.value.find((e) => e.name === payload.floor);
    if (!series) return;

    const idx = series.data.findIndex((d: any) => d.month === payload.month);
    if (idx >= 0) {
        Object.assign(series.data[idx], payload);
    } else {
        const empty = {
            month: payload.month,
            consumption: null,
            confirmed_consumption: null,
            unattributed_consumption: null,
            has_unattributed: false,
        };
        monthlyConsumption.value.forEach((s: any) => s.data.push(s.name === payload.floor ? { ...payload } : { ...empty }));
        if (monthlyConsumption.value[0].data.length > +selectedMonth.value) {
            monthlyConsumption.value.forEach((s: any) => s.data.shift());
        }
    }

    series.latest_data = payload.date;
});

onMounted(() => {
    loadConsumptionData();

    // Subscribe to a private channel for the given meter
    listenEnergy();
});

onUnmounted(() => {
    stopEnergy();
});

// Watch for changes in selecting month
watch(
    selectedMonth,
    () => {
        loadConsumptionData();
    },
    { immediate: true },
);
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="flex w-full flex-row items-center gap-2">
            <span class="fluid-text-sm-base text-primary-text w-full truncate font-bold">{{
                $t('page.admin_dashboard.header.monthly_consumption')
            }}</span>
            <div :class="{ 'pointer-events-none': isLoading }">
                <MonthlyConsumptionFilter :months="months" v-model="selectedMonth" />
            </div>
        </div>

        <Label class="text-secondary-text truncate text-xs font-medium">
            <span :class="['size-2.5 rounded-full', isRecentUpdate(latestUpdated) ? 'bg-success' : 'bg-destructive']"></span>
            {{ $t('page.admin_dashboard.label.last_updated') }}: {{ latestUpdated }}
        </Label>
        <div class="grow">
            <BarChart :loading="isLoading" :option="chartOption" :series="chartSeries" />
        </div>
    </Card>
</template>
