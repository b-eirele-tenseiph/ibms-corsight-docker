<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { devicesClimateMonitor } from '@/routes/admin/dashboard';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    device: string | number | null;
    filter: string;
}>();

const airconTemperature = ref([]);
const isLoading = ref<boolean>(false);

const loadConsumptionData = () => {
    nextTick(() => {
        isLoading.value = true;

        axios
            .get(devicesClimateMonitor.url(), {
                params: {
                    device: props.device,
                    filter: props.filter,
                },
            })
            .then((response) => {
                airconTemperature.value = response.data;
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

const chartOption = computed(() => ({
    type: 'area',
    tooltipConfig: {
        formatter: (params: any) => {
            const date = dayjs(params[0].axisValue).format('MMM D, h:mm A');
            return `${date}<br/>` + params.map((p: any) => `${p.marker} ${p.seriesName}: <b>${p.data[1]}</b><br/>`).join('');
        },
    },
    yAxisConfig: {
        name: '°C',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    xAxisConfig: {
        axisLabel: {
            // format it as time if within day, if past today, include the date
            formatter: (val: any) => (dayjs(val).isSame(dayjs(), 'day') ? dayjs(val).format('HH:mm') : dayjs(val).format('MM/DD HH:mm')),
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

onMounted(() => {
    loadConsumptionData();
});

watch([() => props.device, () => props.filter], loadConsumptionData);
</script>

<template>
    <div class="h-full w-full">
        <div class="h-full w-full">
            <LineChart :option="chartOption" :loading="isLoading" :series="airconTemperature" />
        </div>
    </div>
</template>
