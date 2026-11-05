<script setup lang="ts">
import {
    consumptionData,
    currentData,
    frequency,
    index,
    insights,
    powerData,
    powerFactorData,
    voltageData,
} from '@/routes/admin/electric-monitoring';
import { ElectricMeterProps } from '@/types';
import { router } from '@inertiajs/vue3';
import { getLocalTimeZone, today } from '@internationalized/date';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, onMounted, onUnmounted, reactive } from 'vue';
import { ElectricMonitoringDateTimeFilter, ElectricMonitoringDeviceFilter, ElectricMonitoringInsightsPanel, ElectricMonitoringLineChart } from '.';

const props = defineProps<ElectricMeterProps>();

const filters = reactive({ ...props.filters });

const isCurrentDay = computed(() => {
    return !dayjs(filters.end).isBefore(dayjs(), 'day');
});

const ELECTRIC_METER_DEVICES = computed<string>({
    get: () => filters.device ?? props.electric_meter[0]?.id,
    set: (newValue) => {
        if (newValue) filters.device = newValue;
        else delete filters.device;
    },
});

const loadWithFilters = () => {
    const queryFilters = { ...filters };
    if (queryFilters.device === 'all') {
        queryFilters.device = null;
    }
    router.get(index.url(), queryFilters, {
        preserveState: false,
        replace: true,
        only: [],
    });
};

const DATE: any = reactive({
    value: computed({
        get: () => {
            if (filters.start && filters.end) {
                return { start: filters.start, end: filters.end };
            }
            return filters.time ?? GRAPH_CONTROLS.filterList?.[0]?.value;
        },
        set: (newValue) => {
            if (newValue) {
                filters.time = newValue;
                delete filters.start;
                delete filters.end;
            } else {
                delete filters.time;
            }
            loadWithFilters();
        },
    }),
});

const GRAPH_CONTROLS: any = reactive({
    windowWidth: window.innerWidth,
    isMobileScreen: computed(() => GRAPH_CONTROLS.windowWidth < 768),
    initialize() {
        window.addEventListener('resize', function () {
            GRAPH_CONTROLS.windowWidth = window.innerWidth;
        });
    },
    filterList: [
        {
            text: '6_hrs',
            value: 6,
        },
        {
            text: '1_d',
            value: 24,
        },
        {
            text: '1_w',
            value: 168,
        },
        {
            text: '1_m',
            value: 720,
        },
    ],
    apply(start: any, end: any) {
        Object.assign(props.filters, { start, end });
        DATE.value = null;
        loadWithFilters();
    },
    reset() {
        delete filters['start'];
        delete filters['end'];
        DATE.value = null;
        loadWithFilters();
    },
});

const GRAPH_DATA_ENERGY: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_ENERGY.isLoading = true;
        axios
            .post(consumptionData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
            })
            .then((response) => {
                GRAPH_DATA_ENERGY.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_ENERGY.isLoading = false;
            });
    },
});

const GRAPH_DATA_CURRENT: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_CURRENT.isLoading = true;
        axios
            .post(currentData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
            })
            .then((response) => {
                GRAPH_DATA_CURRENT.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_CURRENT.isLoading = false;
            });
    },
});

const GRAPH_DATA_VOLTAGE: any = reactive({
    isLoading: false,
    currentValue: 'distinct_phase',
    controls: ['distinct_phase', 'between_phase'],
    data: [],
    load: async () => {
        GRAPH_DATA_VOLTAGE.isLoading = true;
        axios
            .post(voltageData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
                phase: GRAPH_DATA_VOLTAGE.currentValue,
            })
            .then((response) => {
                GRAPH_DATA_VOLTAGE.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_VOLTAGE.isLoading = false;
            });
    },
    control: (value: string) => {
        if (!value || value === GRAPH_DATA_VOLTAGE.currentValue) return;
        if (GRAPH_DATA_VOLTAGE.isLoading) return;
        GRAPH_DATA_VOLTAGE.currentValue = value;
        GRAPH_DATA_VOLTAGE.load();
    },
});

const GRAPH_DATA_POWER: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_POWER.isLoading = true;
        axios
            .post(powerData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
            })
            .then((response) => {
                GRAPH_DATA_POWER.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_POWER.isLoading = false;
            });
    },
});

const GRAPH_DATA_POWER_FACTOR: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_POWER_FACTOR.isLoading = true;
        axios
            .post(powerFactorData.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
            })
            .then((response) => {
                GRAPH_DATA_POWER_FACTOR.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_POWER_FACTOR.isLoading = false;
            });
    },
});

const GRAPH_DATA_FREQUENCY: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_FREQUENCY.isLoading = true;
        axios
            .post(frequency.url(), {
                device: ELECTRIC_METER_DEVICES.value,
                time: DATE.value?.start && DATE.value?.end ? { start: DATE.value?.start, end: DATE.value?.end } : DATE.value,
            })
            .then((response) => {
                GRAPH_DATA_FREQUENCY.data = response.data;
            })
            .finally(() => {
                GRAPH_DATA_FREQUENCY.isLoading = false;
            });
    },
});

const INSIGHTS_DATA: any = reactive({
    isLoading: false,
    data: [],
    load: async () => {
        GRAPH_DATA_FREQUENCY.isLoading = true;
        axios
            .post(insights.url(), {
                device: ELECTRIC_METER_DEVICES.value,
            })
            .then((response) => {
                INSIGHTS_DATA.data = response.data;
            })
            .finally(() => {
                INSIGHTS_DATA.isLoading = false;
            });
    },
});

const loadGraphData = async () => {
    if (!ELECTRIC_METER_DEVICES.value) return;

    await Promise.all([
        GRAPH_DATA_ENERGY.load(),
        GRAPH_DATA_CURRENT.load(),
        GRAPH_DATA_VOLTAGE.load(),
        GRAPH_DATA_POWER.load(),
        GRAPH_DATA_POWER_FACTOR.load(),
        GRAPH_DATA_FREQUENCY.load(),
        INSIGHTS_DATA.load(),
    ]);
};

const DATAPOINT_THRESHOLD = computed(() => {
    const hours = parseFloat(DATE.value) || 0;

    if (hours <= 6) return 300;
    if (hours <= 24) return 400;
    if (hours <= 168) return 500;
    return 600;
});

const isRealtime = computed(() => {
    // custom date range always disables realtime
    if (typeof DATE.value === 'object') return false;

    // only enable for 7 days or less (168 hours)
    return parseFloat(DATE.value) <= 168;
});

const currentDate = today(getLocalTimeZone()).toString();

const { stopListening: stopElectricMeter, listen: listenElectricMeter } = useEcho(`admin.electric_meter`, '.electric_meter_data.new', (data: any) => {
    const graphTypes = [GRAPH_DATA_CURRENT, GRAPH_DATA_VOLTAGE, GRAPH_DATA_POWER, GRAPH_DATA_POWER_FACTOR, GRAPH_DATA_FREQUENCY];
    if (DATE.value?.end < currentDate) return;

    data.electric_meter.forEach((e: any) => {
        // update the series data
        graphTypes.forEach((graphData) => {
            if (graphData.data.id === e.device_id) {
                const phase = graphData.data.series.find((p: any) => p.id === e.type);

                if (phase) {
                    phase.data.push([e.received_at, parseFloat(e.context?.value)]);
                    if (phase.data.length >= DATAPOINT_THRESHOLD.value) phase.data.shift();
                }
            }
        });

        // update the insights data
        if (e.type in INSIGHTS_DATA.data) {
            INSIGHTS_DATA.data[e.type] = Number(e.context?.value);
        }

        // update the last data uplink
        const latest = props.electric_meter?.find((dev) => dev.id === e.device_id);
        if (latest) latest.updated_at = e.received_at;
    });
});

const { stopListening: stopEnergy, listen: listenEnergy } = useEcho(`energy_consumption`, '.electric_meter.hourly_data', (data: any) => {
    if (DATE.value?.end < currentDate) return;

    if (GRAPH_DATA_ENERGY.data.id === data.id) {
        const series = GRAPH_DATA_ENERGY.data.series.find((e: any) => e.id === data.alias);
        if (!series) return;

        const date = dayjs(data.hour).format('YYYY-MM-DDTHH:mm:ss.SSSSSS') + 'Z';
        const dataIndex = series.data.findIndex((d: any) => d.x === date);

        if (dataIndex === -1) {
            series.data.push([date, data.value]);
            if (series.data.length >= 300) series.data.shift();
        } else {
            series.data[dataIndex].y = data.value;
        }
    }
});

onMounted(() => {
    if (isRealtime.value) {
        listenElectricMeter();
    }

    listenEnergy();
    loadGraphData();
});

onUnmounted(() => {
    stopElectricMeter();
    stopEnergy();
});
</script>
<template>
    <!-- Filters -->
    <div class="mb-2 flex justify-between border-b">
        <ElectricMonitoringDeviceFilter :electric_meter :filters />
        <ElectricMonitoringDateTimeFilter :electric_meter :filters />
    </div>

    <div class="space-y-4 lg:overflow-auto">
        <!--Insights-->
        <ElectricMonitoringInsightsPanel v-if="isCurrentDay" :data="INSIGHTS_DATA.data" />

        <!-- Graphs -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_ENERGY" />
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_CURRENT" />
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_VOLTAGE" />
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_POWER" />
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_POWER_FACTOR" />
            <ElectricMonitoringLineChart :filters :graphData="GRAPH_DATA_FREQUENCY" />
        </div>
    </div>
</template>
