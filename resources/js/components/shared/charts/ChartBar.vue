<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { ECHARTS_PALETTES } from '@/lib/echarts';
import { ChartSeries } from '@/types';
import { graphic } from 'echarts/core';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    option?: Record<string, any>;
    series: Array<ChartSeries>;
    loading?: boolean;
}>();

const { appearance } = useAppearance();
const palette = computed(() => ECHARTS_PALETTES[appearance.value]);

const isEmpty = computed(() => {
    return !props.loading && (props.series?.length === 0 || props.series?.every((s) => s.data.length === 0));
});

const defaultOption = computed(() => ({
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow',
        },
        valueFormatter: (value: any) => Number(value).toFixed(2),
        ...props.option?.tooltipConfig,
    },
    yAxis: isEmpty.value
        ? {
              show: false,
          }
        : {
              type: 'value',
              ...props.option?.yAxisConfig,
          },
    xAxis: {
        type: 'category',
        axisLine: {
            show: false,
        },
        ...props.option?.xAxisConfig,
    },
    series: props.series?.map((item, i) => {
        const base = palette.value[i % palette.value.length];

        return {
            name: item.name,
            type: 'bar',
            data: item.data,
            itemStyle: {
                borderRadius: [4, 4, 0, 0],
                color: new graphic.LinearGradient(0, 0, 0, 1, [
                    { offset: 0, color: base + '95' },
                    { offset: 1, color: base + '90' },
                ]),
            },
            ...props.option?.seriesConfig,
        };
    }),
    graphic: isEmpty.value
        ? {
              type: 'text',
              left: 'center',
              top: 'middle',
              style: {
                  text: t('state.empty'),
                  fontSize: 18,
                  fill: '#828282',
              },
          }
        : undefined,
    ...props.option?.extras,
}));
</script>

<template>
    <v-chart :loading="loading" :option="defaultOption" :theme="appearance" autoresize style="width: 100%; height: 100%" />
</template>
