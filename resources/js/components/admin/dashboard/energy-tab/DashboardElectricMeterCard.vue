<script setup lang="ts">
import { TrendLineChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { deviceEnergyData } from '@/routes/admin/dashboard';
import { ElectricMeterListData } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc.js';
import { graphic } from 'echarts/core';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

dayjs.extend(timezone);
dayjs.extend(utc);

const { t } = useI18n();

const props = defineProps<{
    meter: { id: string; name: string };
}>();

const emit = defineEmits<{ (e: 'onMouseMove', index: number): void }>();

const consumption = ref<ElectricMeterListData | null>(null);
const isLoading = ref<boolean>(true);

const currentIndex = ref<number>(0);

const lastIndex = computed(() => (consumption.value?.data?.length ?? 1) - 1);

const currentInfo = computed(() => {
    const [label = '', value = 0] = consumption.value?.data[currentIndex.value] || [];
    return { label, value };
});

function handleMouseLeave() {
    setTimeout(() => {
        currentIndex.value = lastIndex.value;
        emit('onMouseMove', lastIndex.value);
    }, 200);
}

const loadConsumptionData = () => {
    isLoading.value = true;
    axios
        .get(deviceEnergyData().url, {
            params: { device: props.meter },
        })
        .then((response) => {
            consumption.value = response.data.energy_data;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const items = computed(() => [
    {
        label: 'page.admin_dashboard.label.consumption',
        unit: 'unit.kwh',
        value: currentInfo.value.value,
    },
    {
        label: 'page.admin_dashboard.label.current',
        unit: 'unit.a',
        value: consumption.value?.current ?? 0,
    },
    {
        label: 'page.admin_dashboard.label.voltage',
        unit: 'unit.v',
        value: consumption.value?.voltage ?? 0,
    },
]);

const chartOption = computed(() => ({
    seriesConfig: {
        areaStyle: {
            color: new graphic.LinearGradient(0, 0, 0, 1, [
                { offset: 0, color: '#ffb55a' + '70' },
                { offset: 1, color: '#ffb55a' + '30' },
            ]),
        },
        itemStyle: {
            color: '#ffb55a' + '80',
        },
        showSymbol: false,
        emphasis: {
            focus: 'series',
            scale: true,
        },
    },
    extras: {
        tooltip: {
            trigger: 'axis',
            formatter: (params: any) => {
                const dataIndex = params?.[0]?.dataIndex;

                if (dataIndex !== null) {
                    currentIndex.value = dataIndex;
                    emit('onMouseMove', dataIndex);
                }

                const axisValue = params?.[0]?.axisValue;
                const date = axisValue ? dayjs(axisValue).format('MMM D, h:mm A') : '';
                let tooltip = `<div style="font-weight: 600; font-size: 12px;">${date}</div>`;

                params?.forEach((p: any) => {
                    const displayValue = p?.data?.[1] ?? t('state.no_uplink');
                    const value = `<span style="font-weight: 600;">${displayValue}</span>`;
                    tooltip += `
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                            ${p.marker ?? ''}
                            <span>${p.seriesName ?? ''}:</span>
                            ${value}
                        </div>`;
                });

                return tooltip;
            },
        },
    },
}));

const latestUpdated = (date: any) => {
    if (!date) return 'N/A';
    const manilaDate = dayjs.utc(date).tz('Asia/Manila');
    return manilaDate.format('MMM DD, YYYY h:mm:ss A');
};

const { stopListening: stopMeter, listen: listenMeter } = useEcho(`admin.electric_meter`, '.electric_meter_data.new', (data: any) => {
    data.electric_meter?.forEach((e: any) => {
        if (!consumption.value || consumption.value.id !== e.device_id) return;

        if (e.type === 'total_current') {
            consumption.value.current = Number(e.context?.value);
        } else if (e.type === 'average_voltage_phase') {
            consumption.value.voltage = Number(e.context?.value);
        }
    });
});

const { stopListening: stopConsumption, listen: listenConsumption } = useEcho('energy_consumption', '.electric_meter.hourly_data', (data: any) => {
    if (data.alias !== 'positive_active_energy') return;

    if (!consumption.value || consumption.value.id !== data.id) return;

    const hour = dayjs(data.hour).tz('Asia/Manila').format('YYYY-MM-DDTHH:mm:ss.SSSSSS') + 'Z';
    const idx = consumption.value.data.findIndex((d: any) => d[0] === hour);

    if (idx >= 0) {
        consumption.value.data[idx][1] = +data.value;
    } else {
        consumption.value.data.push([hour, +data.value]);
        if (consumption.value.data.length > 15) {
            consumption.value.data.shift();
        }
    }
});

onMounted(() => {
    loadConsumptionData();

    listenMeter();
    listenConsumption();
});

onUnmounted(() => {
    stopMeter();
    stopConsumption();
});
</script>

<template>
    <Card class="h-full w-full gap-2 overflow-hidden p-0 pt-4">
        <div class="space-y-2 px-4">
            <!-- Header -->
            <div class="flex flex-col justify-between md:flex-row">
                <div class="mb-1 flex flex-col gap-1">
                    <h3 class="text-sm font-semibold">{{ consumption?.name }}</h3>
                    <p class="text-secondary-text truncate text-xs">{{ consumption?.location }}</p>
                </div>
                <span class="text-success-text bg-success-bg h-fit w-fit rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap">
                    {{ latestUpdated(consumption?.last_updated_at) }}
                </span>
            </div>
            <!-- Current and Monthly Stats -->
            <div class="grid grid-cols-3 gap-1.5">
                <template v-for="(item, index) in items" :key="index">
                    <div class="bg-accent/60 min-w-0 rounded p-1.5">
                        <p class="text-secondary-text mb-0.5 truncate text-xs">{{ $t(item.label) }}</p>
                        <div class="flex min-w-0 flex-wrap items-baseline gap-x-1">
                            <span class="text-sm font-bold">{{ item.value }}</span>
                            <span class="text-secondary-text text-xs">{{ $t(item.unit) }}</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <TrendLineChart
            :option="chartOption"
            :series="[{ name: consumption?.name, data: consumption?.data }]"
            style="width: 100%; height: 100px"
            @mouseleave="handleMouseLeave"
        />
    </Card>
</template>
