<script setup lang="ts">
import { TrendLineChart } from '@/components/shared/charts';
import { useAppearance } from '@/composables/useAppearance';
import type { EnvironmentSensorData } from '@/types';
import dayjs from 'dayjs';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import { graphic } from 'echarts/core';
import { computed, ref, watch } from 'vue';

dayjs.extend(localizedFormat);

const props = defineProps<EnvironmentSensorData>();
const { appearance } = useAppearance();

const emit = defineEmits<{ (e: 'onMouseMove', index: number): void }>();

// Track the currently selected index
const currentIndex = ref(props.seriesData.data.length - 1);

// Compute the currently selected data (label and value)
const currentInfo = computed(() => {
    const [label = '', value = 0] = props.seriesData.data[currentIndex.value] || [];
    return { label, value };
});

const color = computed(() => {
    const theme = appearance.value;
    const value = currentInfo.value.value;

    const palette = {
        dark: { above: '#e36868', below: '#fdba74', normal: '#6ee7b7' },
        light: { above: '#dc2626', below: '#ea580c', normal: '#059669' },
    };

    const state = value > props.threshold.max ? 'above' : value < props.threshold.min ? 'below' : 'normal';

    return palette[theme][state];
});

// Handle mouse move -> update current index
function handleMouseMove(params: any) {
    setTimeout(() => {
        currentIndex.value = params.dataIndex;
        emit('onMouseMove', params.dataIndex);
    }, 200);
}

// Handle mouse leave -> reset to last index
function handleMouseLeave() {
    setTimeout(() => {
        const lastIndex = props.seriesData.data.length - 1;
        currentIndex.value = lastIndex;
        emit('onMouseMove', lastIndex);
    }, 200);
}

// Build chart series from props data
const chartSeries = computed(() => [
    {
        name: props.seriesData.name,
        data: props.seriesData.data.map(([label, value]: any) => ({ name: label, value: [label, value] })),
    },
]);

const chartOption = computed(() => ({
    seriesConfig: {
        areaStyle: {
            color: new graphic.LinearGradient(0, 0, 0, 1, [
                { offset: 0, color: color.value + '70' },
                { offset: 1, color: color.value + '30' },
            ]),
        },
        itemStyle: {
            color: color.value + '80',
        },
    },
}));

// Watch for changes in data length
watch(
    () => props.seriesData.data.length,
    (len) => (currentIndex.value = len - 1),
);
</script>

<template>
    <div class="xs:flex-row xs:gap-4 3xl:flex-col 4xl:flex-row flex w-full flex-col items-center overflow-hidden">
        <div class="xs:w-2/5 3xl:w-full 4xl:w-2/5 flex w-full flex-col">
            <p class="fluid-text-xs-base text-primary-text w-full truncate font-semibold">{{ $t(title) }}</p>
            <p class="text-secondary-text fluid-text-xs-sm line-clamp-2 w-full truncate font-medium whitespace-normal">{{ seriesData.name }}</p>
        </div>

        <div class="xs:w-full 3xl:w-full w-full flex-col items-center gap-1">
            <div class="flex w-full flex-row items-center gap-1">
                <div class="3xl:w-1/3 flex w-1/4 flex-col items-end justify-end" :style="{ color }">
                    <span class="text-xs">{{ unit }}</span>
                    <p class="fluid-text-base-xl min-w-20 text-end leading-none font-bold">
                        {{ currentInfo.value.toFixed(2) }}
                    </p>
                </div>

                <div class="flex w-full flex-1 flex-col overflow-hidden">
                    <TrendLineChart
                        :option="chartOption"
                        :series="chartSeries"
                        style="width: 100%; height: 100px"
                        @mousemove="handleMouseMove"
                        @mouseleave="handleMouseLeave"
                    />
                </div>
            </div>

            <p class="text-secondary-text fluid-text-xs-sm w-full text-right font-medium">
                {{ dayjs(currentInfo.label).format('LLL') }}
            </p>
        </div>
    </div>
</template>
