<script setup lang="ts">
import { totalData } from '@/routes/admin/electric-monitoring';
import { ElectricMeterProps } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, onMounted, onUnmounted, reactive } from 'vue';
import { ElectricMonitoringMetricsCard } from '.';

const props = defineProps<ElectricMeterProps>();

const filters = reactive({ ...props.filters });

const ELECTRIC_METER_DEVICES = computed<string>({
    get: () => filters.device ?? props.electric_meter[0]?.id,
    set: (newValue) => {
        if (newValue) filters.device = newValue;
        else delete filters.device;
    },
});

const latestData = computed(() => props.electric_meter.find((dev: any) => dev.id === ELECTRIC_METER_DEVICES.value)?.updated_at);

const GRAPH_DATA_TOTAL: any = reactive({
    id: null,
    isLoading: false,
    data: [
        {
            label: 'page.electric_monitoring.label.total_consumption',
            key: 'positive_active_energy',
            value: null,
            unit: 'unit.kwh',
        },
        {
            label: 'page.electric_monitoring.label.total_current',
            key: 'total_current',
            value: null,
            unit: 'unit.a',
        },
        {
            label: 'page.electric_monitoring.label.avg_neutral_voltage',
            key: 'average_voltage_phase',
            value: null,
            unit: 'unit.v',
        },
        {
            label: 'page.electric_monitoring.label.avg_phase_voltage',
            key: 'average_voltage_line',
            value: null,
            unit: 'unit.v',
        },
        {
            label: 'page.electric_monitoring.label.total_power',
            key: 'total_active_power',
            value: null,
            unit: 'unit.kw',
        },
        {
            label: 'page.electric_monitoring.label.total_power_factor',
            key: 'power_factor_total',
            value: null,
        },
    ],
    load: async () => {
        if (!ELECTRIC_METER_DEVICES.value) {
            GRAPH_DATA_TOTAL.isLoading = false;
            return;
        }

        GRAPH_DATA_TOTAL.isLoading = true;

        axios
            .post(totalData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
            })
            .then((response) => {
                const responseData = response.data;
                GRAPH_DATA_TOTAL.id = responseData.id;
                GRAPH_DATA_TOTAL.data.forEach((item: any) => (item.value = responseData.data[item.key]));
            })
            .catch(() => {})
            .finally(() => {
                GRAPH_DATA_TOTAL.isLoading = false;
            });
    },
});

const { stopListening: stopCount, listen: listenCount } = useEcho(`admin.electric_meter`, '.electric_meter_data.new', (data: any) => {
    data.electric_meter.forEach((e: any) => {
        if (GRAPH_DATA_TOTAL.id === e.device_id) {
            const total = GRAPH_DATA_TOTAL.data.find((t: any) => t.key === e.type);
            if (total) total.value = parseFloat(e.context.value);
        }
    });
});

onMounted(() => {
    listenCount();

    GRAPH_DATA_TOTAL.load();
});

onUnmounted(() => {
    stopCount();
});
</script>

<template>
    <div class="flex w-full">
        <div class="w-full space-y-2">
            <!-- Last Data Uplink -->
            <div class="inline-flex items-center">
                <span
                    :class="[
                        'me-1 size-2 rounded-full',
                        !latestData ? 'bg-gray-500' : dayjs().diff(dayjs(latestData), 'minute') >= 30 ? 'bg-red-500' : 'bg-green-500',
                    ]"
                ></span>
                <span class="fluid-text-xs-sm font-bold">
                    {{ $t('page.electric_monitoring.label.last_uplink') }}:
                    {{ latestData ? dayjs(latestData).format('MMM D, YYYY h:mm A') : $t('state.no_uplink') }}
                </span>
            </div>

            <!-- Metrics Card -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
                <template v-for="(card, index) in GRAPH_DATA_TOTAL.data" :key="index">
                    <ElectricMonitoringMetricsCard
                        :label="card.label"
                        :value="card.value"
                        :unit="card.unit"
                        :isLoading="GRAPH_DATA_TOTAL.isLoading"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
