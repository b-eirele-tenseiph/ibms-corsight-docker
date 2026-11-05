<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { useLocalStorageRef } from '@/composables/useLocalStorage';
import { useTooltipContentColor } from '@/lib/utils';
import { hourlyEnergyData } from '@/routes/admin/dashboard';
import { DateOption, ElectricMeterData, GapInfo, HourlyEnergyData, MeterPhase } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import minMax from 'dayjs/plugin/minMax';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc.js';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { HourlyConsumptionFilter } from '..';

const props = defineProps<{
    meter: Array<ElectricMeterData>;
}>();

dayjs.extend(minMax);
dayjs.extend(utc);
dayjs.extend(timezone);

const { t } = useI18n();
const { tooltipStyles } = useTooltipContentColor();

const hourlyConsumption = ref<HourlyEnergyData[]>([]);
const isLoading = ref<boolean>(false);

const hours: DateOption[] = [
    { value: '6', label: '6_hour' },
    { value: '12', label: '12_hour' },
    { value: '24', label: '1_day' },
];

const phases: MeterPhase[] = [{ label: 'over_all' }, { label: 'per_phase' }];

// Compute latest updated time from consumption data
const latestUpdated = computed(() => {
    const validDates = hourlyConsumption.value.filter((item) => item.latest_data).map((item) => dayjs.utc(item.latest_data).tz('Asia/Manila'));

    const maxDate = dayjs.max(validDates);
    return maxDate ? maxDate.format('MMM DD YYYY h:mm:ss A') : 'N/A';
});

// Checker if latest update is within the last 30 minutes
const isRecentUpdate = (latestUpdated: any) => {
    if (!latestUpdated) return false;

    const now = dayjs();
    const updatedAt = dayjs(latestUpdated);
    return now.diff(updatedAt, 'minute') <= 30;
};

// Persisted filter state (stored in localStorage)
const filters = useLocalStorageRef('selectedConsumptionFilter', {
    hour: '6',
    phase: 'over_all',
    meters: [props.meter?.[0]],
});

// Load consumption data from backend
const loadConsumptionData = () => {
    nextTick(() => {
        isLoading.value = true;

        axios
            .get(hourlyEnergyData.url(), {
                params: { device: filters.value.meters.map((m) => m?.id), overall: filters.value.phase, hour: filters.value.hour },
            })
            .then((response) => {
                hourlyConsumption.value = response.data.hourly_consumption;
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
            let tooltip = `<div style="font-weight: 600; font-size: 12px;">${date}</div>`;
            let gapInfo: GapInfo | null = null;

            params.forEach((p: any) => {
                const seriesData = hourlyConsumption.value?.[p.seriesIndex]?.series?.[p.dataIndex];
                const value =
                    p.data[1] === 'No Data'
                        ? `<span style="font-style: italic;">${t('state.no_data')}</span>`
                        : `<span style="font-weight: 600;">${p.data[1]} kWh</span>`;

                tooltip += `
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                        ${p.marker}
                        <span>${p.seriesName}:</span>
                        ${value}
                    </div>`;

                if (!gapInfo && seriesData?.hasGap) gapInfo = seriesData.gap_info;
            });

            if (gapInfo) {
                const totalHours = (gapInfo as GapInfo).gap_duration_hours;
                const days = Math.floor(totalHours / 24);
                const hours = totalHours % 24;
                const durationText =
                    days > 0
                        ? `${days} day${days !== 1 ? 's' : ''}${hours > 0 ? ` ${hours} hr${hours !== 1 ? 's' : ''}` : ''}`
                        : `${hours} hour${hours !== 1 ? 's' : ''}`;

                const s = tooltipStyles.value;

                tooltip += `<div style="margin-top: 8px; padding: 8px 10px; background: ${s.bg}; font-size: 12px; border-radius: 4px">
                    <div style="color: ${s.text};">${t('tooltip.offline_duration')} <strong style="color: ${s.strong};">${durationText}</strong></div>
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid ${s.border}; font-size: 11px; color: ${s.strong};">
                        <em>${t('tooltip.offline_note')}</em>
                    </div>
                </div>`;
            }

            return tooltip;
        },
    },
    yAxisConfig: {
        name: 'kWh',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
    xAxisConfig: {
        axisLabel: {
            // format it as time if within day, if past today, include the date
            formatter: (val: any) => (dayjs(val).isSame(dayjs(), 'day') ? dayjs(val).format('h:mm A') : dayjs(val).format('MMM D, h:mm A')),
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

const { stopListening: stopEnergy, listen: listenEnergy } = useEcho('energy_consumption', '.electric_meter.hourly_data', (payload: any) => {
    if (payload.alias !== 'positive_active_energy') return;

    const series = hourlyConsumption.value.find((s: any) => s.id === payload.id);
    if (!series) return;

    const hour = dayjs(payload.hour).tz('Asia/Manila').format('YYYY-MM-DDTHH:mm:ss.SSSSSS') + 'Z';
    const idx = series.data.findIndex((d: any) => d[0] === hour);

    if (idx >= 0) {
        series.data[idx][1] = +payload.value;
    } else {
        hourlyConsumption.value.forEach((s: any) => s.data.push([hour, s.id === payload.id ? +payload.value : null]));

        if (hourlyConsumption.value[0].data.length > +filters.value.hour) {
            hourlyConsumption.value.forEach((s: any) => s.data.shift());
        }
    }
});

onMounted(() => {
    loadConsumptionData();

    listenEnergy();
});

onUnmounted(() => {
    stopEnergy();
});

watch(filters, loadConsumptionData, { deep: true, immediate: true });
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="flex w-full flex-row items-center gap-2">
            <span class="fluid-text-sm-base text-primary-text w-full truncate font-bold">{{
                $t('page.admin_dashboard.header.hourly_consumption')
            }}</span>
            <HourlyConsumptionFilter :meterOptions="meter" :phases="phases" :hours="hours" v-model:filters="filters" />
        </div>

        <Label class="text-secondary-text truncate text-xs font-medium">
            <span :class="['size-2.5 rounded-full', isRecentUpdate(latestUpdated) ? 'bg-success' : 'bg-destructive']"></span>
            {{ $t('page.admin_dashboard.label.last_uplink') }}: {{ latestUpdated }}
        </Label>

        <div class="grow overflow-hidden">
            <LineChart :option="chartOption" :loading="isLoading" :series="hourlyConsumption" />
        </div>
    </Card>
</template>
