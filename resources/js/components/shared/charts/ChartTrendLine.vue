<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    option?: Record<string, any>;
    series: Array<any>;
}>();

const chartOption = computed(() => ({
    xAxis: {
        type: 'time',
        show: false,
        boundaryGap: false,
    },
    yAxis: {
        type: 'value',
        show: false,
    },
    grid: {
        left: 5,
        right: 5,
        top: 5,
        bottom: 5,
    },
    series: props.series?.map((item, idx) => ({
        name: item.name,
        type: 'line',
        smooth: true,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: { width: 2 },
        ...(Array.isArray(props.option?.seriesConfig) ? props.option.seriesConfig[idx] : props.option?.seriesConfig),
        data: item.data,
    })),
    ...props.option?.extras,
}));
</script>

<template>
    <v-chart
        :option="chartOption"
        autoresize
        style="width: 100%; height: 100%"
        @mouseover="$emit('mousemove', $event)"
        @globalout="$emit('mouseleave')"
    />
</template>
