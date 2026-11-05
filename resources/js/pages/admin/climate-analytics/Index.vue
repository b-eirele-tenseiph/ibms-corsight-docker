<script setup lang="ts">
import { buildClimateStats, CLIMATE_THRESHOLDS, ClimateAnalysisPanel, ClimateAnalyticsFilter } from '@/components/admin/climate-analytics';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { actualAndSetpoint, readings as climateReadings, setpoint, temperature } from '@/routes/admin/climate-analytics';
import { speed as fanSpeed } from '@/routes/admin/climate-analytics/fan';
import { status as powerStatus } from '@/routes/admin/climate-analytics/power';
import type { ChartPayload, ClimateFilters, ClimateReading, ClimateReadingsPayload, ClimateRoom } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, onMounted, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage<{ rooms: ClimateRoom[]; filters: Partial<ClimateFilters> }>();
const rooms = computed<ClimateRoom[]>(() => page.props.rooms ?? []);

const filters = computed<ClimateFilters>(() => {
    const query = page.props.filters ?? {};

    const room = query.room || String(rooms.value[0]?.id ?? '');
    const roomAreas = rooms.value.find((r) => String(r.id) === room)?.room_areas ?? [];
    const roomArea = query.room_area || String(roomAreas[0]?.id ?? '');

    return {
        time: query.time ?? null,
        start: query.start ?? null,
        end: query.end ?? null,
        room,
        room_area: roomArea,
        resolution: query.resolution ?? 'hour',
    };
});

const resolution = computed(() => filters.value.resolution);

const selectedArea = computed(() =>
    rooms.value.find((room) => String(room.id) === filters.value.room)?.room_areas.find((area) => String(area.id) === filters.value.room_area),
);

const chartParams = () => ({
    room: filters.value.room,
    room_area: filters.value.room_area,
    ...(filters.value.start && filters.value.end ? { start: filters.value.start, end: filters.value.end } : { time: filters.value.time ?? 24 }),
});

const SETPOINT_AND_ACTUAL = reactive({ loading: true, data: null as ChartPayload | null });
const FAN_SPEED = reactive({ loading: true, data: null as ChartPayload | null });
const POWER_STATUS = reactive({ loading: true, data: null as ChartPayload | null });
const TEMPERATURE = reactive({ loading: true, data: null as ChartPayload | null });
const SETPOINT = reactive({ loading: true, data: null as ChartPayload | null });
const READINGS = reactive({ data: [] as ClimateReading[] });

const load = (panel: { loading: boolean; data: ChartPayload | null }, url: string) => {
    panel.loading = true;
    axios
        .get(url, { params: chartParams() })
        .then(({ data }) => {
            panel.data = data;
        })
        .catch(() => {
            panel.data = null;
        })
        .finally(() => {
            panel.loading = false;
        });
};

const loadReadings = () => {
    axios
        .get<ClimateReadingsPayload>(climateReadings.url(), { params: chartParams() })
        .then(({ data }) => {
            READINGS.data = data.readings ?? [];
        })
        .catch(() => {
            READINGS.data = [];
        });
};

const loadAll = () => {
    load(SETPOINT_AND_ACTUAL, actualAndSetpoint.url());
    load(FAN_SPEED, fanSpeed.url());
    load(POWER_STATUS, powerStatus.url());
    load(TEMPERATURE, temperature.url());
    load(SETPOINT, setpoint.url());
    loadReadings();
};

onMounted(loadAll);

// Refetch when filters change (component instance persists across visits).
watch(
    () => page.props.filters,
    () => {
        loadAll();
    },
);

const readings = computed(() => [...READINGS.data].sort((a, b) => dayjs.utc(a.timestamp).valueOf() - dayjs.utc(b.timestamp).valueOf()));

const lastUplink = computed(() => selectedArea.value?.last_uplink ?? null);
const isOffline = computed(
    () => !lastUplink.value || dayjs().diff(dayjs.utc(lastUplink.value), 'minute') >= CLIMATE_THRESHOLDS.minReadingIntervalMinutes,
);
const stats = computed(() => buildClimateStats(readings.value, filters.value, t, isOffline.value));

const toSeries = (panel: { data: ChartPayload | null }) =>
    panel.data?.datasets?.map((ds) => ({ name: ds.label, color: ds.borderColor, data: ds.data })) ?? [];

const fanSeries = computed(() =>
    toSeries(FAN_SPEED).map((series) => ({
        ...series,
        data: series.data.map((value: number | null) => (value !== null && Number(value) >= 4 ? 0 : value)),
    })),
);

const axisLabelFormatter = (value: string) => {
    const timestamp = dayjs(value);

    if (!timestamp.isValid()) {
        return value;
    }

    if (resolution.value === 'day') {
        return timestamp.format('MMM D');
    }

    return timestamp.hour() === 0 && timestamp.minute() === 0 ? timestamp.format('MMM D') : timestamp.format('h:mm A');
};

const chartOption = (panel: { data: ChartPayload | null }, extra: Record<string, any> = {}) => ({
    xAxisConfig: {
        data: panel.data?.labels ?? [],
        axisLabel: { formatter: axisLabelFormatter },
    },
    extras: {
        axisPointer: {
            label: {
                formatter: (params: any) => (params.axisDimension === 'x' ? axisLabelFormatter(String(params.value)) : String(params.value)),
            },
        },
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
    legendConfig: {
        show: (panel.data?.datasets?.length ?? 0) > 1,
    },
    ...extra,
});

const lowestValue = (panel: { data: ChartPayload | null }): number | null => {
    const values =
        panel.data?.datasets?.flatMap((dataset) => dataset.data).filter((value): value is number => value !== null && value !== undefined) ?? [];

    return values.length ? Math.min(...values) : null;
};

const temperatureAxis = (panel: { data: ChartPayload | null }) => {
    const lowest = lowestValue(panel);

    return {
        name: '°C',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
        ...(lowest === null ? {} : { min: Math.floor(lowest - 1) }),
    };
};

const powerLabel = (value: number | null | undefined): string => {
    if (value === null || value === undefined) {
        return '-';
    }

    return Number(value) === 1 ? t('page.climate_analytics.state.on') : t('page.climate_analytics.state.off');
};

const powerAxis = computed(() => ({
    min: 0,
    max: 1,
    interval: 1,
    axisLabel: {
        formatter: (value: number) => powerLabel(value),
    },
}));

const powerTooltip = computed(() => ({
    valueFormatter: (value: number | null) => powerLabel(value),
}));

const fanLabel = (value: number | string | null | undefined): string => {
    const numeric = Number(value);

    if (value === null || value === undefined || Number.isNaN(numeric)) {
        return '-';
    }

    return numeric === 0
        ? t('page.climate_analytics.fan.auto')
        : numeric === 1
          ? t('page.climate_analytics.fan.low')
          : numeric === 2
            ? t('page.climate_analytics.fan.med')
            : numeric === 3
              ? t('page.climate_analytics.fan.high')
              : numeric === 4
                ? t('page.climate_analytics.fan.auto')
                : '-';
};

const fanAxis = () => ({
    min: 0,
    max: 3,
    interval: 1,
    axisLabel: {
        formatter: fanLabel,
    },
});

const fanTooltip = {
    valueFormatter: (value: number | null) => fanLabel(value),
};

const bucketLabel = (label: string): string => {
    const [date, time = '00:00'] = label.split(' ');
    const hour = parseInt(time.slice(0, 2), 10);

    if (resolution.value === 'day') {
        return `${date} 00:00`;
    }

    if (resolution.value === 'six_hour') {
        return `${date} ${String(Math.floor(hour / 6) * 6).padStart(2, '0')}:00`;
    }

    return `${date} ${time.slice(0, 5)}`;
};

const appendPoint = (panel: { data: ChartPayload | null }, payload: any, value: number, index = 0) => {
    if (!panel.data) return;

    const position = panel.data.labels.indexOf(bucketLabel(String(payload.labels)));

    if (position === -1) return;

    const dataset = panel.data.datasets[index];

    if (dataset) {
        dataset.data[position] = value;
    }
};

const isCurrentArea = (payload: any): boolean =>
    String(payload.room_id ?? '') === String(filters.value.room) && String(payload.room_area_id ?? '') === String(filters.value.room_area);

const isCurrentRange = (): boolean => {
    if (!filters.value.start || !filters.value.end) {
        return true;
    }

    const now = dayjs();

    return now.isAfter(dayjs(filters.value.start).startOf('day')) && now.isBefore(dayjs(filters.value.end).endOf('day'));
};

const appendLiveReading = (type: 'temperature' | 'current_setpoint' | 'slave_power' | 'fan_speed', value: unknown) => {
    if (!isCurrentRange()) {
        return;
    }

    const timestamp = dayjs().utc().toISOString();
    const minute = timestamp.slice(0, 16);
    let reading = READINGS.data.find((item) => item.timestamp.slice(0, 16) === minute);

    if (!reading) {
        reading = { timestamp };
        READINGS.data.push(reading);
    }

    if (type === 'temperature') {
        reading.temperature = Number(value);
    } else if (type === 'current_setpoint') {
        reading.setpoint = Number(value);
    } else if (type === 'slave_power') {
        reading.power = String(value).toLowerCase() === 'on' ? 'on' : 'off';
    } else {
        const fan = String(value).toLowerCase();
        reading.fan_speed = fan === 'auto' ? 'auto' : Number(value);
    }
};

const climateData = (topic: string, onPayload: (payload: any) => void) => {
    useEcho('admin.climate_data', `.${topic}`, (payload: any) => {
        if (!isCurrentArea(payload)) {
            return;
        }

        onPayload(payload);
    });
};

// Fan speed
climateData('climate_data.fan_speed.new', (payload) => {
    appendPoint(FAN_SPEED, payload, String(payload.value).toLowerCase() === 'auto' ? 4 : Number(payload.value));
    appendLiveReading('fan_speed', payload.value);
});

// Current setpoint → Setpoint + Setpoint & Actual (first dataset)
climateData('climate_data.current_setpoint.new', (payload) => {
    appendPoint(SETPOINT, payload, Number(payload.value));
    appendPoint(SETPOINT_AND_ACTUAL, payload, Number(payload.value), 0);
    appendLiveReading('current_setpoint', payload.value);
});

// Temperature → Temperature + Setpoint & Actual (second dataset)
climateData('climate_data.temperature.new', (payload) => {
    appendPoint(TEMPERATURE, payload, Number(payload.value));
    appendPoint(SETPOINT_AND_ACTUAL, payload, Number(payload.value), 1);
    appendLiveReading('temperature', payload.value);
});

// Slave power → ON/OFF
climateData('climate_data.slave_power.new', (payload) => {
    appendPoint(POWER_STATUS, payload, String(payload.value).toLowerCase() === 'on' ? 1 : 0);
    appendLiveReading('slave_power', payload.value);
});
</script>

<template>
    <Head :title="$t('head.climate_analytics')" />
    <AdminLayout title="head.climate_analytics">
        <AdminPageLayout class="gap-8">
            <!-- Filters -->
            <ClimateAnalyticsFilter :rooms="rooms" :filters="filters" />

            <!-- Setpoint & Actual -->
            <ClimateAnalysisPanel
                :title="$t('page.climate_analytics.header.setpoint_and_actual')"
                :series="toSeries(SETPOINT_AND_ACTUAL)"
                :option="chartOption(SETPOINT_AND_ACTUAL, { yAxisConfig: temperatureAxis(SETPOINT_AND_ACTUAL) })"
                :loading="SETPOINT_AND_ACTUAL.loading"
                :stats="stats.setpointAndActual"
            />

            <!-- Remaining panels -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <ClimateAnalysisPanel
                    :title="$t('page.climate_analytics.header.fan_speed')"
                    :series="fanSeries"
                    :option="
                        chartOption(FAN_SPEED, {
                            yAxisConfig: fanAxis(),
                            seriesConfig: { step: 'middle' },
                            tooltipConfig: fanTooltip,
                        })
                    "
                    :loading="FAN_SPEED.loading"
                    :stats="stats.fanSpeed"
                />
                <ClimateAnalysisPanel
                    :title="$t('page.climate_analytics.header.on_off_patterns')"
                    :series="toSeries(POWER_STATUS)"
                    :option="
                        chartOption(POWER_STATUS, {
                            yAxisConfig: powerAxis,
                            seriesConfig: { step: 'start' },
                            tooltipConfig: powerTooltip,
                        })
                    "
                    :loading="POWER_STATUS.loading"
                    :stats="stats.power"
                />
                <ClimateAnalysisPanel
                    :title="$t('page.climate_analytics.header.temperature_fluctuations')"
                    :series="toSeries(TEMPERATURE)"
                    :option="chartOption(TEMPERATURE, { yAxisConfig: temperatureAxis(TEMPERATURE) })"
                    :loading="TEMPERATURE.loading"
                    :stats="stats.temperature"
                />
                <ClimateAnalysisPanel
                    :title="$t('page.climate_analytics.header.setpoint_analysis')"
                    :series="toSeries(SETPOINT)"
                    :option="chartOption(SETPOINT, { yAxisConfig: temperatureAxis(SETPOINT) })"
                    :loading="SETPOINT.loading"
                    :stats="stats.setpoint"
                />
            </div>
        </AdminPageLayout>
    </AdminLayout>
</template>
