<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { CarbonTrend } from '@/types';
import { computed } from 'vue';

defineProps<{
    trend: Array<CarbonTrend>;
}>();

const chartOption = computed(() => ({
    type: 'line',
    tooltipConfig: {
        show: false,
    },
    yAxisConfig: {
        name: 'Tons',
        nameLocation: 'start',
        nameGap: 28,
        nameTextStyle: {
            fontWeight: 'bold',
        },
    },
}));
</script>

<template>
    <div class="p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ $t('page.electric_monitoring_report.header.weekly_emission_trend') }}</h3>
        <div class="h-[450px] rounded-sm border bg-white pb-2">
            <LineChart :option="chartOption" :series="trend" />
        </div>
    </div>
</template>
