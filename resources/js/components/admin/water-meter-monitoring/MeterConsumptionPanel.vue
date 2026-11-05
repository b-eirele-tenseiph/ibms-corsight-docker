<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { ChartSeries, DateOption } from '@/types';
import dayjs from 'dayjs';
import minMax from 'dayjs/plugin/minMax';
import utc from 'dayjs/plugin/utc.js';
import { computed, ref } from 'vue';
import { MeterConsumptionPanelFilter } from '.';

dayjs.extend(minMax);
dayjs.extend(utc);

const generateRawWaterUsage = (): Array<[string, number]> => {
    const today = dayjs();
    const daysInMonth = today.daysInMonth();
    const data: Array<[string, number]> = [];

    for (let i = daysInMonth - 1; i >= 0; i--) {
        const date = today.subtract(i, 'day').startOf('day');

        const baseUsage = 165;
        const trend = (daysInMonth - 1 - i) * 0.3;
        const noise = (Math.random() - 0.5) * 30;
        const usage = parseFloat((baseUsage + trend + noise).toFixed(1));

        data.push([date.toISOString(), usage]);
    }

    return data;
};

const isLoading = ref<boolean>(false);
const rawWaterUsage = generateRawWaterUsage();

const selectedHours = ref<string>('168');

const days: Array<DateOption> = [
    { value: '168', label: 'last_7_days' },
    { value: '720', label: 'last_30_days' },
];

const dailyWaterUsage = computed<Array<ChartSeries>>(() => {
    const daysCount = Number(selectedHours.value) / 24;

    return [
        {
            name: 'All Water Meter',
            data: rawWaterUsage.slice(-daysCount),
        },
    ];
});

const chartOption = computed(() => ({
    type: 'area',
    tooltipConfig: {
        formatter: (params: any) => {
            const date = dayjs(params[0].axisValue).format('MMM D, h:mm A');
            return `${date}<br/>` + params.map((p: any) => `${p.marker} ${p.seriesName}: <b>${p.data[1]} m³</b><br/>`).join('');
        },
    },
    legendConfig: {
        show: false,
    },
    yAxisConfig: {
        name: 'm³',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    xAxisConfig: {
        axisLabel: {
            formatter: (val: any) => dayjs(val).format('MMM D'),
        },
    },
    extras: {
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
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="mb-4 flex w-full flex-row justify-between">
            <div class="flex flex-col gap-1">
                <span class="fluid-text-sm-base font-bold">{{ $t('page.water_monitoring.header.water_consumption') }}</span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{
                    $t('page.water_monitoring.header.water_consumption_subtitle')
                }}</span>
            </div>
            <div :class="{ 'pointer-events-none': isLoading }">
                <MeterConsumptionPanelFilter :days="days" v-model="selectedHours" />
            </div>
        </div>

        <div class="grow">
            <LineChart :option="chartOption" :loading="isLoading" :series="dailyWaterUsage" />
        </div>
    </Card>
</template>
