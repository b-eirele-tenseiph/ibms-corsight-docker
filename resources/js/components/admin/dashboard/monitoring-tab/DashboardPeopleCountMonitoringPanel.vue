<script setup lang="ts">
import { BarChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { visitorData } from '@/routes/admin/dashboard';
import { Icon } from '@iconify/vue';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const graphData = ref([
    {
        name: 'People Count',
        data: [] as Array<[]>,
    },
]);

const counters = ref({
    daily: 0,
    total: 0,
});

const isLoading = ref<boolean>(false);

const chartOption = computed(() => ({
    xAxisConfig: {
        axisLabel: {
            formatter: function (value: any, index: any) {
                if (index === 0) {
                    return dayjs(value).format('MMM D');
                }
                return dayjs(value).format('HH:mm');
            },
        },
    },
    yAxisConfig: {
        name: useI18n().t('page.admin_dashboard.label.visitor'),
        nameTextStyle: {
            align: 'left',
        },
        max: (v: any) => {
            if (!Number.isFinite(v.max)) return 5; // safe default for empty data
            return Math.ceil(v.max) + 1;
        },
    },
    extras: {
        grid: {
            top: 30,
        },
    },
}));

// Load device data from backend
const loadData = () => {
    nextTick(() => {
        isLoading.value = true;

        axios
            .get(visitorData.url())
            .then((response) => {
                counters.value = response.data.counter;
                graphData.value[0].data = response.data.graph;
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

const { stopListening: stopVisitor, listen: listenVisitor } = useEcho(`admin.booth_visitor`, '.count.updated', (data: any) => {
    // Update counters
    counters.value.daily = data.daily;
    counters.value.total = data.total;

    // Update graph
    const newDataPoint = data.hourly;

    const existingIndex = graphData.value[0].data.findIndex((point) => point[0] === newDataPoint[0]);
    if (existingIndex >= 0) {
        // Update existing point
        graphData.value[0].data[existingIndex] = newDataPoint;
    } else {
        // Add new point
        graphData.value[0].data.push(newDataPoint);
    }
});

onMounted(() => {
    loadData();

    // listeners
    listenVisitor();
});

onUnmounted(() => {
    stopVisitor();
});
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="flex w-full flex-col">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.visitor_trend') }}</span>
        </div>

        <div class="3xl:flex-row flex h-full flex-col sm:flex-row">
            <div class="3xl:flex-col 3xl:w-3xs flex w-full flex-row items-center justify-center gap-8 py-4 sm:w-2xs sm:flex-col">
                <div class="flex flex-col items-center justify-center">
                    <Icon icon="mdi:human-handsdown" class="text-secondary-text size-6" />
                    <div class="fluid-text-xs-sm text-secondary-text font-medium">{{ $t('page.admin_dashboard.label.daily_visitor') }}</div>
                    <div class="text-primary-text text-2xl font-bold">{{ counters.daily }}</div>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <Icon icon="mdi:human-male-female-child" class="text-secondary-text size-6" />
                    <div class="fluid-text-xs-sm text-secondary-text font-medium">{{ $t('page.admin_dashboard.label.total_visitor') }}</div>
                    <div class="text-primary-text text-2xl font-bold">{{ counters.total }}</div>
                </div>
            </div>

            <div class="w-full grow">
                <BarChart :option="chartOption" :loading="isLoading" :series="graphData" />
            </div>
        </div>
    </Card>
</template>
