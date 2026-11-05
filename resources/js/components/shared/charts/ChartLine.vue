<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { ECHARTS_PALETTES } from '@/lib/echarts';
import { graphic } from 'echarts/core';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    option?: Record<string, any>;
    series: Array<any>;
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
        boundaryGap: false,
        ...props.option?.xAxisConfig,
    },
    series: props.series?.map((item, i) => {
        const base = item.color ?? palette.value[i % palette.value.length];

        return {
            name: item.name,
            type: 'line',
            data: item.data,
            symbolSize: (val: any, params: any) => (item.series?.[params.dataIndex]?.hasGap ? 18 : 5),
            symbol: (val: any, params: any) => (item.series?.[params.dataIndex]?.hasGap ? 'diamond' : 'circle'),
            ...(props.option?.type === 'area' && {
                areaStyle: {
                    color: new graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: base + '70' },
                        { offset: 1, color: base + '40' },
                    ]),
                },
            }),
            ...props.option?.seriesConfig,
        };
    }),
    legend: {
        show: true,
        icon: 'roundRect',
        type: 'scroll',
        orient: 'horizontal',
        bottom: 0,
        itemGap: 20,
        itemWidth: 15,
        itemHeight: 10,
        data: props.series?.map((item) => item.name),
        ...props.option?.legendConfig,
    },
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
