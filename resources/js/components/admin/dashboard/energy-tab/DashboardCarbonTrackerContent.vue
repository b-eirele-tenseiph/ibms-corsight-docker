<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { ScrollArea } from '@/components/ui/scroll-area';
import { CarbonFootPrintData, ElectricMeterData } from '@/types';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import { computed } from 'vue';

const props = defineProps<{
    carbonFootprint: CarbonFootPrintData | null;
    meter: ElectricMeterData;
    isLoading: boolean;
}>();

const targetKg = computed(() => Number(props.carbonFootprint?.target_kg ?? 0));

const targetPct = computed(() => {
    if (!targetKg.value || !props.carbonFootprint) return 0;
    return Math.min(Math.round((props.carbonFootprint.total_carbon_kg / targetKg.value) * 100), 100);
});

const vsLastMonth = computed<number>(() => {
    const current = props.carbonFootprint?.total_carbon_kg ?? 0;
    const previous = props.carbonFootprint?.prev_total_carbon_kg ?? 0;

    if (!previous) return 0;
    return parseFloat((((current - previous) / previous) * 100).toFixed(2));
});

const maxCO2 = computed(() => {
    const meters = props.carbonFootprint?.meters ?? [];
    return meters.reduce((max: number, m: any) => Math.max(max, m.carbon_kg), 1);
});

const remaining = computed(() => {
    return targetKg.value - (props.carbonFootprint?.total_carbon_kg ?? 0);
});

const status = computed(() => {
    if (targetPct.value >= 90) return 'danger';
    if (targetPct.value >= 70) return 'warning';
    return 'safe';
});

const statusTextClass = computed(() => ({ safe: 'text-success-text', warning: 'text-warning-text', danger: 'text-error-text' })[status.value]);

const progressClass = computed(() => ({ safe: '[&>div]:bg-success', warning: '[&>div]:bg-warning', danger: '[&>div]:bg-error' })[status.value]);
</script>

<template>
    <Card class="flex h-full flex-col gap-4 overflow-hidden">
        <CardHeader class="shrink-0 gap-0 border-b px-4 [.border-b]:pb-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.carbon_tracker') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">
                {{ carbonFootprint?.month ?? dayjs().format('MMMM YYYY') }} · {{ meter.length }} {{ $t('page.admin_dashboard.label.active_meter') }}
            </span>
        </CardHeader>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <CardContent v-if="carbonFootprint" class="flex min-h-0 flex-1 flex-col gap-3 overflow-hidden px-0">
                <div class="space-y-3 px-4">
                    <!--This Month Card-->
                    <div class="bg-success-bg border-success-border relative shrink-0 overflow-hidden rounded-xl border p-4">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <Icon icon="lucide:leaf" class="text-success-text/70 size-3.5" />
                                <span class="text-success-text/70 text-xs font-bold">{{ $t('date_range_filter.this_month') }}</span>
                            </div>
                            <div
                                :class="['flex items-center gap-1 text-xs font-semibold', vsLastMonth > 0 ? 'text-error-text' : 'text-success-text']"
                            >
                                <Icon :icon="vsLastMonth > 0 ? 'lucide:arrow-up-right' : 'lucide:arrow-down-right'" class="size-3.5" />
                                {{ Math.abs(vsLastMonth) }}%
                                <span class="text-secondary-text font-normal">{{ $t('page.admin_dashboard.label.vs_last_mo') }}</span>
                            </div>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="text-success-text fluid-text-xl-2xl leading-none font-bold tracking-tight tabular-nums">
                                {{ carbonFootprint?.total_carbon_kg?.toFixed(2) ?? '—' }}
                            </span>
                            <span class="text-success-text fluid-text-sm-base leading-tight font-semibold">{{ $t('unit.kg_co2') }}</span>
                        </div>
                        <div class="text-secondary-text mt-2 flex items-center text-xs">
                            <span>
                                {{ $t('page.admin_dashboard.label.grid_factor') }}
                                <span class="text-primary-text font-medium"
                                    >{{ carbonFootprint?.emission_factor }} {{ $t('unit.kg_kwh') }}</span
                                ></span
                            >
                        </div>
                    </div>

                    <!--Monthly Target Card-->
                    <div class="bg-accent/40 shrink-0 space-y-3 rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-secondary-text flex items-center gap-1.5">
                                <Icon icon="lucide:target" class="size-3.5" />
                                <span class="text-xs font-semibold">{{ $t('page.admin_dashboard.label.monthly_target') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span :class="['fluid-text-xs-sm font-bold tabular-nums', statusTextClass]">{{ targetPct }}%</span>
                                <span class="text-secondary-text text-xs">/ {{ targetKg.toLocaleString('en-PH') }} {{ $t('unit.kg') }}</span>
                            </div>
                        </div>
                        <div class="relative">
                            <Progress :model-value="targetPct" :class="['bg-secondary h-2.5 rounded-full', progressClass]" />
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <Icon
                                :icon="Number(remaining) < 0 ? 'lucide:circle-alert' : 'lucide:circle-check'"
                                :class="['size-3', Number(remaining) < 0 ? 'text-error-text' : 'text-success-text']"
                            />
                            <span :class="['font-semibold', Number(remaining) < 0 ? 'text-error-text' : 'text-success-text']">
                                {{ remaining.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }} {{ $t('unit.kg') }}
                            </span>
                            <span class="text-secondary-text">{{ $t('page.admin_dashboard.label.remaining') }}</span>
                        </div>
                    </div>
                </div>

                <!--Meter Breakdown Card-->
                <div class="flex min-h-0 flex-1 flex-col px-2 pb-6">
                    <div class="text-secondary-text mb-2 flex shrink-0 items-center gap-1.5 px-2">
                        <Icon icon="lucide:gauge" class="size-3.5" />
                        <span class="text-xs font-semibold tracking-wide">{{ $t('page.admin_dashboard.label.meter_breakdown') }}</span>
                    </div>

                    <ScrollArea class="h-full border-t border-dotted px-1">
                        <div v-for="m in carbonFootprint?.meters" :key="m.id" class="px-2 py-3">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="fluid-text-xs-sm text-primary-text truncate font-medium">{{ m.name }}</span>
                                <Badge
                                    variant="secondary"
                                    class="bg-success-bg text-success-text rounded-md px-1.5 text-xs font-semibold tabular-nums"
                                >
                                    {{ m.carbon_kg.toFixed(2) }} {{ $t('unit.kg') }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2">
                                <Progress
                                    :model-value="Math.round((m.carbon_kg / maxCO2) * 100)"
                                    class="bg-secondary [&>div]:bg-success h-1.5 flex-1"
                                />
                                <span class="text-secondary-text shrink-0 text-right text-xs tabular-nums"
                                    >{{ m.total_kwh }} {{ $t('unit.kwh') }}</span
                                >
                            </div>
                        </div>
                    </ScrollArea>
                </div>
            </CardContent>
            <div v-else class="py-10">
                <StateEmpty />
            </div>
        </template>
    </Card>
</template>
