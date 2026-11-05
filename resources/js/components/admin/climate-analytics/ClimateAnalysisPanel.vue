<script setup lang="ts">
import { LineChart } from '@/components/shared/charts';
import type { ClimateChip, ClimateStat } from '@/types';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';
import { badge } from './config';

const props = defineProps<{
    title: string;
    series: Array<any>;
    option?: Record<string, any>;
    loading?: boolean;
    stats?: ClimateStat[];
    chips?: ClimateChip[];
}>();

const chipsToStats = (chips?: ClimateChip[]): ClimateStat[] =>
    (chips ?? []).map((chip) => ({
        label: chip.label,
        value: chip.value,
        unit: chip.unit,
        caption: chip.suffix,
        badge: chip.badge
            ? {
                  kind: badge(chip.badge.tone ?? 'secondary').kind,
                  text: chip.badge.label,
                  hint: chip.badge.label,
              }
            : undefined,
    }));

const limitBadges = (stats: ClimateStat[], maximum = 2): ClimateStat[] => {
    const candidates = stats
        .map((stat, index) => (stat.badge ? { index, priority: badge(stat.badge.kind).priority } : null))
        .filter((candidate): candidate is { index: number; priority: number } => candidate !== null)
        .sort((a, b) => b.priority - a.priority || a.index - b.index)
        .slice(0, maximum)
        .map((candidate) => candidate.index);

    const keep = new Set(candidates);

    return stats.map((stat, index) => (keep.has(index) ? stat : { ...stat, badge: undefined }));
};

const displayStats = computed(() => limitBadges(props.stats?.length ? props.stats : chipsToStats(props.chips)));
</script>

<template>
    <div class="flex flex-col gap-4 px-2">
        <span class="fluid-text-sm-base font-bold">{{ title }}</span>

        <dl v-if="displayStats.length" class="grid gap-x-6 gap-y-5" style="grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr))">
            <div v-for="stat in displayStats" :key="stat.label" class="flex min-w-0 flex-col gap-1">
                <dt class="text-secondary-text flex min-w-0 items-center gap-1.5 text-xs leading-tight font-semibold tracking-wide uppercase">
                    <span v-if="stat.color" class="size-2 shrink-0 rounded-full" :style="{ backgroundColor: stat.color }" aria-hidden="true"></span>
                    <span class="min-w-0">{{ stat.label }}</span>
                </dt>

                <dd class="flex min-w-0 flex-col gap-1">
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="fluid-text-sm-base font-semibold tabular-nums">{{ stat.value }}</span>
                        <span v-if="stat.unit" class="fluid-text-xs-sm text-secondary-text font-medium">{{ stat.unit }}</span>
                        <span
                            v-if="stat.badge"
                            class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="badge(stat.badge.kind).class"
                            :title="stat.badge.hint"
                        >
                            <Icon :icon="badge(stat.badge.kind).icon" class="size-3 shrink-0" aria-hidden="true" />
                            {{ stat.badge.text }}
                        </span>
                    </div>

                    <span v-if="stat.caption" class="text-secondary-text text-xs capitalize">{{ stat.caption }}</span>
                </dd>
            </div>
        </dl>

        <div class="relative h-80 w-full">
            <div class="h-full w-full">
                <LineChart :option="option" :series="series" :loading="loading" />
            </div>
        </div>
    </div>
</template>
