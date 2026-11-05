<script setup lang="ts">
import { CarbonSummary } from '@/types';
import { Icon } from '@iconify/vue';

withDefaults(
    defineProps<{
        summary: CarbonSummary | undefined;
        days: number;
    }>(),
    {
        summary: (): CarbonSummary => ({
            total_co2_emissions: 0,
            carbon_intensity: 0,
            previous_period: 0,
            reduction_percent: 0,
        }),
    },
);
</script>

<template>
    <div class="border-b p-6">
        <h3 class="text-lg font-semibold">
            {{ $t('page.electric_monitoring_report.header.emission_summary') }}
        </h3>
    </div>

    <div class="space-y-4 p-6">
        <!-- Total CO2 Emissions -->
        <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
            <p class="mb-2 text-xs font-medium tracking-wide text-emerald-700 uppercase">
                {{ $t('page.electric_monitoring_report.label.total_co2_emissions') }}
            </p>
            <p class="text-4xl font-bold text-emerald-700">
                {{ summary?.total_co2_emissions.toLocaleString() }}
                <span class="text-lg font-normal text-emerald-600">{{ $t('unit.metric_ton') }}</span>
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <!-- Carbon Intensity -->
            <div class="bg-accent/40 rounded-lg border p-4">
                <div class="mb-3 flex gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <Icon icon="mdi:chart-bar" class="size-4 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $t('page.electric_monitoring_report.label.carbon_intensity') }}
                        </p>
                        <p class="text-secondary-text text-xs">
                            {{ $t('page.electric_monitoring_report.label.grid_emission_factor') }}
                        </p>
                    </div>
                </div>
                <p class="text-2xl font-bold">
                    {{ summary?.carbon_intensity.toLocaleString() }}
                    <span class="text-secondary-text text-sm font-normal">{{ $t('unit.kg_c02_per_kwh') }}</span>
                </p>
            </div>

            <!-- Previous Period -->
            <div class="bg-accent/40 rounded-lg border p-4">
                <div class="mb-3 flex gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-yellow-50">
                        <Icon icon="mdi:clock-outline" class="size-4 text-yellow-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $t('page.electric_monitoring_report.label.previous_period') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $t('page.electric_monitoring_report.label.last_days_from_end', { days }) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-2xl font-bold">
                        {{ summary?.previous_period.toLocaleString() }}
                        <span class="text-secondary-text text-sm font-normal">{{ $t('unit.tons_co2e') }}</span>
                    </p>
                    <div
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5',
                            (summary?.reduction_percent ?? 0) < 0 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700',
                        ]"
                    >
                        <Icon :icon="(summary?.reduction_percent ?? 0) >= 0 ? 'mdi:arrow-down-circle' : 'mdi:arrow-up-circle'" class="size-4" />
                        <span class="text-sm font-bold"> {{ Math.abs(summary?.reduction_percent ?? 0).toLocaleString() }}% </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
