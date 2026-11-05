<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { electricConsumptionData } from '@/routes/admin/dashboard';
import { ElectricityConsumption } from '@/types';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { computed, nextTick, onMounted, ref } from 'vue';

const energyConsumption = ref<Array<ElectricityConsumption>>([]);
const isLoading = ref<boolean>(false);

const change = computed(() => {
    const prev = energyConsumption.value[0]?.total ?? 0;
    const current = energyConsumption.value[1]?.total ?? 0;

    if (prev === 0) {
        if (current === 0) return 0;
        return 100;
    }

    const percentage = ((current - prev) / prev) * 100;
    return Math.round(percentage * 10) / 10;
});

const chartOption = computed(() => ({
    xAxisConfig: {
        axisLine: {
            show: false,
        },
        axisTick: {
            show: true,
            inside: false,
        },
        splitLine: {
            show: true,
        },
        axisLabel: {
            formatter: function (value: any, index: any) {
                return index == 0 ? value : value.replace('Week ', '');
            },
        },
    },
    yAxisConfig: {
        splitLine: {
            show: false,
        },
        axisLabel: {
            formatter: (value: any) => (value >= 1000 ? (value / 1000).toFixed(1) + 'K' : value),
            margin: 10,
        },
        axisLine: {
            show: energyConsumption.value?.every((item) => item.data?.length === 0) ? false : true,
        },
    },
}));

const loadConsumptionData = () => {
    nextTick(() => {
        isLoading.value = true;

        axios
            .get(electricConsumptionData.url())
            .then((response) => {
                energyConsumption.value = response.data.consumption;
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

onMounted(() => {
    loadConsumptionData();
});
</script>

<template>
    <Card class="flex h-full flex-col gap-2 p-4">
        <div class="flex w-full flex-col">
            <span class="fluid-text-sm-base font-bold">{{ $t('page.admin_dashboard.header.electricity_consumption') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{
                $t('page.admin_dashboard.header.electricity_consumption_subtitle')
            }}</span>
        </div>

        <div v-if="energyConsumption?.length > 0" :class="[change > 0 ? 'text-destructive' : 'text-success', 'flex flex-row items-center gap-2']">
            <Icon :icon="change > 0 ? 'tabler:trending-up' : 'tabler:trending-down'" class="size-4" />
            <p class="fluid-text-xs-sm font-semibold">{{ Math.abs(change) + '%' }} {{ $t('page.admin_dashboard.label.than_last_month') }}</p>
        </div>

        <div class="min-h-0 grow">
            <LineChart :loading="isLoading" :option="chartOption" :series="energyConsumption" />
        </div>

        <template v-if="energyConsumption?.length > 0">
            <Separator />
            <div class="grid grid-cols-2 divide-x py-4">
                <div class="flex flex-col items-center justify-center gap-2 text-center">
                    <Label class="text-secondary-text">{{ dayjs().subtract(2, 'month').format('MMMM YYYY') }}</Label>
                    <Label class="fluid-text-sm-base font-bold">₱ {{ energyConsumption[0]?.total.toLocaleString() }}</Label>
                </div>
                <div class="flex flex-col items-center justify-center gap-2 text-center">
                    <Label class="text-secondary-text">{{ dayjs().subtract(1, 'month').format('MMMM YYYY') }}</Label>
                    <Label class="fluid-text-sm-base font-bold">₱ {{ energyConsumption[1]?.total.toLocaleString() }}</Label>
                </div>
            </div>
        </template>

        <p class="fluid-text-xxs-xs text-secondary-muted-text text-right italic">{{ $t('page.admin_dashboard.label.price_vary') }}</p>
    </Card>
</template>
