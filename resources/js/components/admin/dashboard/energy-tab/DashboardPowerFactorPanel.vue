<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import Badge from '@/components/ui/badge/Badge.vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import Progress from '@/components/ui/progress/Progress.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { devicePowerFactorData } from '@/routes/admin/dashboard';
import { ElectricMeterData, PowerFactorData } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{ meter: ElectricMeterData[] }>();

const powerFactorData = ref<Array<PowerFactorData>>([]);
const isLoading = ref<boolean>(true);

const loadData = () => {
    isLoading.value = true;
    axios
        .get(devicePowerFactorData.url(), {
            params: { device_ids: props.meter.map((m: any) => m.id) },
        })
        .then((r) => {
            powerFactorData.value = r.data.power_factor;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const fleetAverage = computed(() => {
    if (!powerFactorData.value.length) return null;
    const sum = powerFactorData.value.reduce((acc, d) => acc + (d.power_factor ?? 0), 0);
    return sum / powerFactorData.value.length;
});

const getConfig = (data: number) => {
    if (data >= 0.95)
        return {
            label: 'status.excellent',
            badgeClass: 'bg-success-bg text-success-text ',
            valueClass: 'text-success-text',
            progress: '[&>div]:bg-success',
        };
    if (data >= 0.85)
        return {
            label: 'status.good',
            badgeClass: 'bg-info-bg text-info-text',
            valueClass: 'text-info-text',
            progress: '[&>div]:bg-info',
        };
    if (data >= 0.75)
        return {
            label: 'status.fair',
            badgeClass: 'bg-warning-bg text-warning-text ',
            valueClass: 'text-warning-text',
            progress: '[&>div]:bg-warning',
        };
    return {
        label: 'status.poor',
        badgeClass: 'bg-error-bg text-error-text',
        valueClass: 'text-error-text',
        progress: '[&>div]:bg-error',
    };
};

const fleetAveragePercent = computed(() => (fleetAverage.value ?? 0) * 100);

const fleetMeta = computed(() => (fleetAverage.value !== null ? getConfig(fleetAverage.value) : getConfig(0)));

const { stopListening: stopMeter, listen: listenMeter } = useEcho(`admin.electric_meter`, '.electric_meter_data.new', (data: any) => {
    data?.electric_meter?.forEach((e: any) => {
        const meter = powerFactorData.value.find((i) => i.id == e.device_id);
        if (meter && e.type == 'power_factor_total') {
            meter.power_factor = Number(e.context?.value);
        }
    });
});

onMounted(() => {
    listenMeter();
});

onUnmounted(() => {
    stopMeter();
});

watch(
    () => props.meter,
    (val) => {
        if (val?.length) loadData();
    },
    { immediate: true },
);
</script>

<template>
    <Card class="flex h-full flex-col gap-4 overflow-hidden">
        <CardHeader class="shrink-0 gap-0 border-b px-4 [.border-b]:pb-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.power_factor') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{ $t('page.admin_dashboard.header.power_factor_subtitle') }}</span>
        </CardHeader>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>

        <template v-else-if="powerFactorData.length === 0">
            <div class="flex h-full items-center justify-center py-10">
                <StateEmpty />
            </div>
        </template>

        <template v-else>
            <CardContent class="flex min-h-0 flex-1 flex-col gap-0 px-2 pb-0">
                <!-- Meter list -->
                <ScrollArea class="min-h-0 flex-1 px-4">
                    <div v-for="data in powerFactorData" :key="data.id" class="group flex items-center gap-4 py-3 transition-colors">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="fluid-text-xs-sm text-primary-text truncate font-medium transition-colors">
                                    {{ data.name }}
                                </span>
                                <span class="fluid-text-xs-sm shrink-0 font-bold tabular-nums" :class="getConfig(data.power_factor ?? 0).valueClass">
                                    {{ data.power_factor?.toFixed(2) ?? '—' }}
                                </span>
                            </div>

                            <Progress
                                :model-value="(data.power_factor ?? 0) * 100"
                                class="bg-secondary h-1.5"
                                :class="[getConfig(data.power_factor ?? 0).progress]"
                            />
                        </div>

                        <Badge class="text-xs font-semibold" :class="getConfig(data.power_factor ?? 0).badgeClass">
                            {{ $t(getConfig(data.power_factor ?? 0).label) }}
                        </Badge>
                    </div>
                </ScrollArea>

                <Separator class="my-4" />

                <!-- Fleet average -->
                <div class="shrink-0 px-3">
                    <div class="bg-accent/40 space-y-2 rounded-xl border p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="text-secondary-text space-y-1">
                                <p class="text-sm font-bold tracking-wide">{{ $t('page.admin_dashboard.header.fleet_average') }}</p>
                                <p class="text-xs font-medium">{{ $t('page.admin_dashboard.header.fleet_average_subtitle') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="fluid-text-base-xl leading-none font-black tabular-nums" :class="fleetMeta.valueClass">
                                    {{ fleetAverage?.toFixed(2) ?? '—' }}
                                </p>

                                <Badge class="text-xs font-semibold" :class="fleetMeta.badgeClass">
                                    {{ $t(fleetMeta.label) }}
                                </Badge>
                            </div>
                        </div>

                        <Progress :model-value="fleetAveragePercent" class="bg-secondary h-2" :class="[fleetMeta.progress]" />
                    </div>
                </div>
            </CardContent>
        </template>
    </Card>
</template>
