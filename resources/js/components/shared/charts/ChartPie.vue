<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { ECHARTS_PALETTES } from '@/lib/echarts';
import { ChartSeries } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    option?: Record<string, any>;
    series: Array<ChartSeries>;
    loading?: boolean;
}>();

const { appearance } = useAppearance();
const palette = computed(() => ECHARTS_PALETTES[appearance.value]);

const defaultOption = computed(() => ({
    textStyle: {
        fontWeight: '600',
    },
    tooltip: {
        show: false,
    },
    legend: {
        left: 'center',
        bottom: 10,
        ...props.option?.legend,
    },
    series: [
        {
            type: 'pie',
            avoidLabelOverlap: false,
            emphasis: {
                ...props.option?.emphasis,
            },
            labelLine: {
                show: false,
            },
            data: (props.series ?? []).map((d: any, i: number) => {
                const base = palette.value[i % palette.value.length];
                return {
                    ...d,
                    itemStyle: { color: base + 95 },
                };
            }),
            ...props.option?.seriesConfig,
        },
    ],
    ...props.option?.extras,
}));
</script>

<template>
    <v-chart :loading="loading" :option="defaultOption" :theme="appearance" autoresize style="width: 100%; height: 100%" />
</template>
