<script setup lang="ts">
import { TrendLineChart } from '@/components/shared/charts';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { DataGrid } from '@/components/user/dashboard';
import { useAppearance } from '@/composables/useAppearance';
import { TimeSeriesDevice } from '@/types';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import { graphic } from 'echarts/core';
import { computed, ref, watch } from 'vue';

dayjs.extend(utc);
dayjs.extend(timezone);

const props = defineProps<{
    data: TimeSeriesDevice;
}>();

const { appearance } = useAppearance();

const emit = defineEmits<{ (e: 'onMouseMove', index: number): void }>();

const sensorData = ref([
    { label: 'Temperature', value: 0, unit: '°C', color: { light: '#c23531', dark: '#ff6f61' } },
    { label: 'Illuminance', value: 0, unit: '%', color: { light: '#73C0DE', dark: '#409EFF' } },
]);

const timestamp = ref<string>('');

const currentIndex = ref(0);

function currentInfo(index: number) {
    const getData = (name: string) => props.data.series.find((s) => s.name === name)?.data[index] ?? [];
    const [temp, illum] = ['temperature', 'illuminance'].map(getData);

    timestamp.value = temp[0] || illum[0] || '';
    [temp[1], illum[1]].forEach((val, i) => (sensorData.value[i].value = val ?? 0));

    currentIndex.value = index;
}

function handleMouseMove(params: any) {
    currentInfo(params.dataIndex);
    emit('onMouseMove', params.dataIndex);
}

function handleMouseLeave() {
    currentInfo(0);
    emit('onMouseMove', 0);
}

watch(
    () => props.data,
    () => handleMouseLeave(),
    { immediate: true },
);

const chartOption = computed(() => ({
    seriesConfig: props.data.series.map((_, i) => {
        const base = sensorData.value[i]?.color?.[appearance.value];
        return {
            areaStyle: {
                color: new graphic.LinearGradient(0, 0, 0, 1, [
                    { offset: 0, color: base + '70' },
                    { offset: 1, color: base + '30' },
                ]),
            },
            itemStyle: { color: base + '80' },
        };
    }),
}));
</script>
<template>
    <Card class="xs:p-4 bg-controls-default size-full p-3">
        <CardContent class="flex size-full flex-grow flex-col gap-2 p-0">
            <DataGrid :sensorData="sensorData" />
            <Label class="text-secondary-text justify-end truncate text-xs font-medium">
                {{ dayjs(timestamp).tz('Asia/Manila').format('MMM D YYYY hh:mm A') }}</Label
            >
            <div class="flex-grow">
                <TrendLineChart :option="chartOption" :series="data?.series" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" />
            </div>
        </CardContent>
    </Card>
</template>
