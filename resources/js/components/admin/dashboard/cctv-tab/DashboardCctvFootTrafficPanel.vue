<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { CctvTimeRange, CorsightRecentLog } from '@/types';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = withDefaults(
    defineProps<{
        data: CorsightRecentLog[];
        isLoading: boolean;
        range?: CctvTimeRange;
    }>(),
    {
        range: 'today',
    },
);

const { t } = useI18n();

// Buckets follow the selected range: Today → 24 hourly points; 7/30 days →
// one point per calendar day. Every event is an entry (bucketed by enter_at);
// exits bucket by exit_at.
const seriesData = computed(() => {
    if (props.range === 'today') {
        const entered = Array<number>(24).fill(0);
        const exited = Array<number>(24).fill(0);

        for (const event of props.data) {
            entered[dayjs(event.timestamp).hour()] += 1;
            if (event.exit_at) {
                exited[dayjs(event.exit_at).hour()] += 1;
            }
        }

        const labels = Array.from({ length: 24 }, (_, hour) => `${String(hour).padStart(2, '0')}:00`);
        const toPoints = (counts: number[]) => labels.map((label, index) => [label, counts[index]]);

        return { daily: false, entered: toPoints(entered), exited: toPoints(exited) };
    }

    const dayCount = props.range === 'last_7_days' ? 7 : 30;
    const days = Array.from({ length: dayCount }, (_, index) => {
        const day = dayjs().subtract(dayCount - 1 - index, 'day');
        return { key: day.format('YYYY-MM-DD'), label: day.format('MMM D'), entered: 0, exited: 0 };
    });
    const bucketIndex = new Map(days.map((day, index) => [day.key, index]));

    const bump = (iso: string, field: 'entered' | 'exited') => {
        const index = bucketIndex.get(dayjs(iso).format('YYYY-MM-DD'));
        if (index !== undefined) {
            days[index][field] += 1;
        }
    };

    for (const event of props.data) {
        bump(event.timestamp, 'entered');
        if (event.exit_at) {
            bump(event.exit_at, 'exited');
        }
    }

    return {
        daily: true,
        entered: days.map((day) => [day.label, day.entered]),
        exited: days.map((day) => [day.label, day.exited]),
    };
});

const busiest = computed(() => {
    let max = 0;
    let label: string | null = null;
    seriesData.value.entered.forEach(([name, count]) => {
        if (count > max) {
            max = count;
            label = name;
        }
    });
    return max > 0 ? label : null;
});

const chartSeries = computed(() => [
    { name: t('page.admin_dashboard.cctv.label.entered_badge'), color: '#2d98a4', data: seriesData.value.entered },
    { name: t('page.admin_dashboard.cctv.label.exited_badge'), color: '#d47b25', data: seriesData.value.exited },
]);

const chartOption = computed(() => ({
    type: 'area',
    xAxisConfig: {
        axisLabel: {
            // Explicit identity formatter — v-chart merges options between
            // range switches, so a stale formatter from the previous mode
            // would otherwise leak into this one.
            formatter: (value: string) => value,
            interval: seriesData.value.daily ? (props.range === 'last_30_days' ? 'auto' : 0) : 2,
        },
    },
    yAxisConfig: {
        splitNumber: 4,
    },
    legendConfig: {
        icon: 'circle',
        itemWidth: 8,
        itemHeight: 8,
        right: 0,
        top: 0,
        bottom: 'auto',
    },
    seriesConfig: {
        smooth: true,
        showSymbol: false,
        lineStyle: { width: 2 },
    },
    extras: {
        grid: {
            left: 8,
            right: 8,
            top: 28,
            bottom: 4,
            containLabel: true,
        },
    },
}));
</script>

<template>
    <Card class="flex h-full flex-col gap-2 p-4">
        <div class="flex w-full flex-col">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.cctv.header.foot_traffic') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{ $t('page.admin_dashboard.cctv.header.foot_traffic_subtitle') }}</span>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <template v-if="data.length > 0">
                <div class="min-h-0 flex-1">
                    <LineChart :option="chartOption" :series="chartSeries" />
                </div>
                <div v-if="busiest" class="text-secondary-text flex items-center gap-1 text-xs font-semibold">
                    <Icon :icon="seriesData.daily ? 'lucide:calendar' : 'lucide:clock'" class="size-3.5" />
                    {{ $t(seriesData.daily ? 'page.admin_dashboard.cctv.label.busiest_day' : 'page.admin_dashboard.cctv.label.busiest_hour', { time: busiest }) }}
                </div>
            </template>
            <div v-else class="flex flex-1 items-center justify-center">
                <StateEmpty label="page.admin_dashboard.cctv.label.no_logs" />
            </div>
        </template>
    </Card>
</template>
