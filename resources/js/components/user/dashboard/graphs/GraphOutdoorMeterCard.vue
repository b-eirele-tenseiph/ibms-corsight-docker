<script setup lang="ts">
import { TrendLineChart } from '@/components/shared/charts';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { TimeSeriesDevice } from '@/types';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import { computed, ref, watch } from 'vue';

dayjs.extend(utc);
dayjs.extend(timezone);

const props = defineProps<{
    data: TimeSeriesDevice;
}>();

const emit = defineEmits<{ (e: 'onMouseMove', index: number): void }>();

const sensorData = ref([
    { label: 'Temperature', value: 0, unit: '°C' },
    { label: 'Humidity', value: 0, unit: '%' },
]);

const timestamp = ref<string>('');

const currentInfo = (index: number) => {
    const temp = props.data.series.find((s) => s.name === 'temperature')?.data[index] ?? [];
    const hum = props.data.series.find((s) => s.name === 'humidity')?.data[index] ?? [];

    timestamp.value = temp[0] || hum[0] || '';

    sensorData.value[0].value = temp[1] ?? 0;
    sensorData.value[1].value = hum[1] ?? 0;
};

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
    seriesConfig: {
        areaStyle: { opacity: 0.3 },
    },
}));
</script>

<template>
    <Card class="xs:p-4 bg-controls-default h-full p-3">
        <CardContent class="flex size-full flex-grow flex-col gap-2 p-0">
            <div class="flex flex-col gap-2">
                <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">{{ data?.name }}</Label>
                <Label class="text-secondary-text text-xs font-medium"> {{ dayjs(timestamp).tz('Asia/Manila').format('MMM D YYYY hh:mm A') }}</Label>
            </div>

            <div class="flex h-full w-full flex-row gap-2">
                <div class="flex w-1/3 flex-col">
                    <div v-for="sensor in sensorData" :key="sensor.label" class="flex h-full flex-row items-center gap-2">
                        <Icon
                            :icon="sensor.label.toLowerCase().includes('temperature') ? 'carbon:temperature-celsius' : 'carbon:humidity-alt'"
                            class="size-5 sm:size-6"
                        />
                        <span class="text-sm font-bold">
                            {{ sensor.value }}
                            <sup v-if="sensor.unit" class="text-xxs">{{ sensor.unit }}</sup>
                        </span>
                    </div>
                </div>
                <div class="h-full w-2/3">
                    <TrendLineChart :option="chartOption" :series="data?.series" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
