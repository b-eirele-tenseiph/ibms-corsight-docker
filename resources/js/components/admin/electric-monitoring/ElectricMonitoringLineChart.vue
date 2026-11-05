<script lang="ts" setup>
import { LineChart } from '@/components/shared/charts';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger } from '@/components/ui/select';
import { ElectricMeterGraphData, QueryParams } from '@/types';
import dayjs from 'dayjs';
import { SelectValue } from 'reka-ui';
import { computed, reactive } from 'vue';

const props = defineProps<{
    filters?: QueryParams;
    graphData: ElectricMeterGraphData;
}>();

const filters = reactive({ ...props.filters });

const xAxisFormatter = (value: string) => {
    const dt = dayjs(value);
    const timeNum = Number(filters.time) || 0;

    if (timeNum <= 168 && timeNum > 0) {
        return dt.hour() === 0 && dt.minute() === 0 ? dt.format('MMM D') : dt.format('h:mm A');
    }
    if (timeNum <= 720 && timeNum > 0) {
        return dt.format('MMM D');
    }
    if (filters.end && filters.start) {
        return dt.format('MMM D');
    }
    return dt.format('h:mm A');
};

const chartOption = computed(() => ({
    type: 'area',
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
                myTool1: {
                    show: true,
                    title: 'Download CSV',
                    icon: 'image://https://www.svgrepo.com/show/354932/document-excel.svg',
                    onclick: function () {
                        downloadCSV();
                    },
                },
            },
        },
    },
    seriesConfig: {
        showSymbol: false,
        lineStyle: { width: 1.5 },
        emphasis: {
            focus: 'series',
            scale: true,
            blurScope: 'coordinateSystem',
        },
    },
    xAxisConfig: {
        type: 'time',
        axisLabel: {
            formatter: xAxisFormatter,
            color: '#6B7281',
            overflow: 'truncate',
            interval: 'auto',
        },
    },
    yAxisConfig: {
        name: props.graphData.data.unit,
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    legendConfig: {
        textStyle: { color: '#6B7280' },
        formatter: (name: string) => {
            const series = props.graphData.data.series;
            const val = series.findLast((s) => s.name === name || `(${s.id})` === name)?.data.at(-1)?.[1];
            const unit = props.graphData.data.unit || '';

            return `${name}: ${val ?? 0} ${unit}`;
        },
    },
}));

const downloadCSV = () => {
    const { series, title = 'Chart Data' } = props.graphData.data;

    const rows = series.flatMap((s) =>
        s.data.map((item) =>
            [s.name, dayjs(Array.isArray(item) ? item[0] : item.name).format('YYYY-MM-DD'), Array.isArray(item) ? item[1] : item.value].join(','),
        ),
    );

    const csvContent = [`Title: ${title}`, 'Series,Date,Value', ...rows].join('\n');

    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
    link.download = `${title.replace(/\s+/g, '_')}.csv`;
    link.click();
};
</script>

<template>
    <div class="flex h-full flex-col gap-2 px-2">
        <div class="flex items-center justify-between">
            <h2 class="fluid-text-xs-sm font-semibold">
                {{ props.graphData.data.title }}
            </h2>

            <div class="flex justify-end space-x-2">
                <template v-if="graphData.data.title === $t('page.electric_monitoring.header.voltage')">
                    <Select :modelValue="graphData.currentValue" @update:modelValue="graphData.control" class="h-12">
                        <SelectTrigger>
                            <div class="xs:block hidden">
                                <SelectValue />
                            </div>
                        </SelectTrigger>
                        <SelectContent align="end">
                            <SelectGroup>
                                <SelectItem v-for="volt in graphData.controls" :key="volt" :value="volt">
                                    {{ $t(`filter.${volt}`) }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </template>
            </div>
        </div>

        <div class="relative h-80 w-full">
            <div class="h-full w-full">
                <LineChart :option="chartOption" :loading="props.graphData.isLoading" :series="props.graphData.data?.series ?? []" />
            </div>
        </div>
    </div>
</template>
