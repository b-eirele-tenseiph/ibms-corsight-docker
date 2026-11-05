<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { ChartSeries } from '@/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    option?: Record<string, any>;
    series: Array<ChartSeries>;
    loading?: boolean;
}>();

const { appearance } = useAppearance();

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
    series: props.series?.map((item) => {
        return {
            name: item.name,
            type: 'scatter',
            data: item.data,
            symbolSize: 5,
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
        data: props.series?.map((item) => item.name),
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
