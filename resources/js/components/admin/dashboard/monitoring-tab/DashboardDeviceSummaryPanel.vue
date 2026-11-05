<script setup lang="ts">
import { PieChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { useDeviceSummary } from '@/composables/useDeviceSummary';
import { computed, onMounted } from 'vue';

const { deviceSummary, isLoading, loadDeviceSummary } = useDeviceSummary();

const pieData = computed(() =>
    deviceSummary.value.map((device: any) => ({
        name: device.name,
        value: device.percentage,
        active: device.active,
        total: device.total,
    })),
);

const chartOption = computed(() => ({
    legend: {
        itemGap: 20,
        formatter: (name: string) => {
            const item = pieData.value.find((d: any) => d.name === name);
            return item ? `${item.value}% ${name}` : name;
        },
    },
    emphasis: {
        label: {
            show: true,
            formatter: (params: any) => {
                return `${params.name}\n${params.data.active}/${params.data.total}`;
            },
        },
    },
    seriesConfig: {
        radius: ['40%', '55%'],
        center: ['50%', '38%'],
        label: {
            show: false,
            position: 'center',
            lineHeight: 15,
            fontWeight: 'bold',
        },
    },
}));

onMounted(() => {
    loadDeviceSummary();
});
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="flex w-full flex-col">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.device_summary') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{ $t('page.admin_dashboard.header.device_summary_subtitle') }}</span>
        </div>

        <div class="grow">
            <PieChart :loading="isLoading" :option="chartOption" :series="pieData" />
        </div>
    </Card>
</template>
