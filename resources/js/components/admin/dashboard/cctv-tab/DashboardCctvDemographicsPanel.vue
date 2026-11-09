<script setup lang="ts">
import { PieChart } from '@/components/shared/charts';
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { CorsightDemographics } from '@/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    data: CorsightDemographics;
    isLoading: boolean;
}>();

const { t } = useI18n();

const percent = (count: number) => (props.data.total > 0 ? Math.round((count / props.data.total) * 100) : 0);

const chartSeries = computed(() => [
    { name: t('page.admin_dashboard.cctv.label.female'), value: props.data.gender.female },
    { name: t('page.admin_dashboard.cctv.label.male'), value: props.data.gender.male },
    { name: t('page.admin_dashboard.cctv.label.unknown'), value: props.data.gender.unknown },
]);

const chartOption = computed(() => ({
    legend: {
        show: true,
        bottom: 0,
        icon: 'circle',
        itemWidth: 8,
        itemHeight: 8,
        formatter: (name: string) => {
            const item = chartSeries.value.find((d: any) => d.name === name);
            return item ? `${name} ${percent(item.value)}%` : name;
        },
    },
    seriesConfig: {
        radius: ['62%', '84%'],
        center: ['50%', '44%'],
        label: { show: false },
        labelLine: { show: false },
    },
    extras: {
        backgroundColor: 'transparent',
    },
}));
</script>

<template>
    <Card class="h-full gap-2 p-4">
        <div class="flex w-full items-center justify-between gap-2">
            <div class="flex flex-col">
                <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.cctv.header.demographics') }}</span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{
                    $t('page.admin_dashboard.cctv.header.demographics_subtitle')
                }}</span>
            </div>
            <Badge variant="secondary" class="fluid-text-xs-sm shrink-0 font-semibold">
                {{ $t('page.admin_dashboard.cctv.label.total_detections', { count: data.total }) }}
            </Badge>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="data.total > 0" class="flex min-h-0 flex-1 flex-col gap-4">
                <!-- Gender donut -->
                <div class="flex h-full min-h-40 flex-col items-center 2xl:justify-center">
                    <div class="2xl:-w-56 relative w-full max-w-44 flex-1 2xl:h-56 2xl:flex-none">
                        <PieChart :option="chartOption" :series="chartSeries" style="width: 100%; height: 100%" />
                        <div class="pointer-events-none absolute inset-x-0 top-[44%] flex -translate-y-1/2 flex-col items-center justify-center">
                            <span class="text-primary-text text-2xl leading-none font-bold">{{ data.total }}</span>
                            <span class="text-secondary-text text-xs font-medium">
                                {{ $t('page.admin_dashboard.cctv.label.visitors') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Age distribution -->
                <div class="flex w-full flex-col justify-center gap-3 2xl:min-w-0 2xl:flex-1">
                    <span class="text-secondary-text fluid-text-xs-base font-bold">
                        {{ $t('page.admin_dashboard.cctv.label.age_distribution') }}
                    </span>
                    <div v-for="age in data.age" :key="age.range" class="flex flex-col gap-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold">{{ age.range }}</span>
                            <span class="text-secondary-text font-medium">{{ percent(age.count) }}%</span>
                        </div>
                        <div class="bg-muted h-2 w-full overflow-hidden rounded-full">
                            <div class="bg-primary h-full rounded-full" :style="{ width: `${percent(age.count)}%` }" />
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="flex flex-1 items-center justify-center">
                <StateEmpty label="page.admin_dashboard.cctv.label.no_logs" />
            </div>
        </template>
    </Card>
</template>
