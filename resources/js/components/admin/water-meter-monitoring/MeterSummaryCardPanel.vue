<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { MetricConfig, MetricsData } from '@/types';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

const data = ref<MetricsData>({
    today_usage: {
        total: 41.37,
        percentage: 12,
    },
    weekly_avg: {
        total: 5234,
        percentage: 5,
    },
    monthly_total: {
        total: 34963.2,
        percentage: 8,
    },
    active_meter: {
        active: 5,
        total: 50,
        percentage: 100,
    },
});

const metricConfig: Record<keyof MetricsData, MetricConfig> = {
    today_usage: {
        label: 'today_usage',
        icon: 'solar:waterdrops-outline',
        unit: 'm³',
        description: 'today_usage_subtitle',
    },
    weekly_avg: {
        label: 'weekly_avg',
        icon: 'solar:chart-2-outline',
        unit: 'm³',
        description: 'weekly_avg_subtitle',
    },
    monthly_total: {
        label: 'monthly_total',
        icon: 'solar:wallet-money-outline',
        description: 'monthly_total_subtitle',
    },
    active_meter: {
        label: 'active_meter',
        icon: 'solar:transmission-outline',
        description: 'active_meter_subtitle',
    },
};
</script>

<template>
    <div class="grid h-full grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="(value, key) in data" :key="key" class="relative justify-center gap-2 p-3">
            <!-- Percentage Badge -->
            <div
                v-if="value.percentage !== undefined"
                class="absolute top-3 right-3 rounded-full bg-green-100 px-1.5 py-0.5 text-xs font-semibold text-green-600"
            >
                +{{ value.percentage }}%
            </div>

            <!-- Icon & Label -->
            <div class="text-secondary-text flex flex-row items-center gap-2">
                <Icon :icon="metricConfig[key]?.icon" class="size-4 shrink-0" />
                <div class="fluid-text-xs-sm w-full truncate overflow-hidden font-semibold whitespace-nowrap">
                    {{ $t(`page.water_monitoring.label.${metricConfig[key]?.label}`) }}
                </div>
            </div>

            <!-- Value -->
            <div class="flex items-center gap-1 font-bold">
                <span class="text-2xl">
                    <span v-if="'active' in value">{{ value.active }} / </span>
                    <span v-if="key === 'monthly_total'">₱</span>
                    {{ value.total.toLocaleString() }}
                </span>
                <sup class="text-secondary-text">{{ metricConfig[key]?.unit }}</sup>
            </div>

            <!-- Description -->
            <div class="text-secondary-muted-text text-xs font-medium">
                {{ $t(`page.water_monitoring.label.${metricConfig[key]?.description}`) }}
            </div>
        </Card>
    </div>
</template>
