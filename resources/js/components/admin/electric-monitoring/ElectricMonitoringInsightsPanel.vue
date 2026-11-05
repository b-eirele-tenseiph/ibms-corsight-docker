<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{ data: Record<string, number> }>();

const SCHEMA: Record<string, any> = {
    current_unbalance: {
        id: 'current',
        levels: [
            { state: 'critical', test: (v: number) => v > 10 },
            { state: 'warning', test: (v: number) => v >= 5 },
        ],
    },
    total_power_factor: {
        id: 'power_factor',
        warnIcon: 'lucide:trending-up',
        levels: [
            { state: 'critical', test: (v: number) => v < 0.85 },
            { state: 'warning', test: (v: number) => v < 0.95 },
        ],
    },
    voltage_unbalance: {
        id: 'voltage',
        levels: [
            { state: 'critical', test: (v: number) => v > 3 },
            { state: 'warning', test: (v: number) => v >= 1 },
        ],
    },
    frequency: {
        id: 'frequency',
        warnIcon: 'lucide:activity',
        levels: [
            { state: 'critical', test: (v: number) => v < 59.5 || v > 60.5 },
            { state: 'warning', test: (v: number) => v < 59.9 || v > 60.1 },
        ],
    },
};

const THEMES: Record<string, any> = {
    good: {
        cls: 'border-success-border bg-success-bg text-success-text',
        iconCls: 'bg-success-bg text-success-text',
        icon: 'lucide:check-circle',
    },
    warning: {
        cls: 'border-warning-border bg-warning-bg text-warning-text',
        iconCls: 'bg-warning-bg text-warning-text',
        icon: 'lucide:alert-circle',
    },
    critical: {
        cls: 'border-error-border bg-error-bg text-error-text',
        iconCls: 'bg-error-bg text-error-text',
        icon: 'lucide:alert-triangle',
    },
};

const insights = computed(() =>
    Object.entries(props.data)
        .filter(([key]) => SCHEMA[key])
        .map(([key, val]) => {
            const config = SCHEMA[key];

            const match = config.levels.find((l: any) => l.test(val));
            const state = match?.state || 'good';
            const theme = THEMES[state];

            return {
                title: `page.electric_monitoring.insights.${config.id}.title`,
                desc: `page.electric_monitoring.insights.${config.id}.${state}`,
                icon: (state === 'warning' && config.warnIcon) || theme.icon,
                classes: theme,
                value: val,
            };
        }),
);
</script>

<template>
    <div class="space-y-2 px-2">
        <h3 class="text-lg font-semibold">{{ $t('page.electric_monitoring.header.insight') }}</h3>
        <div class="grid gap-2 sm:grid-cols-2">
            <div v-for="item in insights" :key="item.title" :class="['rounded-lg border p-3', item.classes.cls]">
                <div class="flex gap-3">
                    <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-full', item.classes.iconCls]">
                        <Icon :icon="item.icon" />
                    </div>

                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ $t(item.title) }}</p>
                        <p class="text-xs">{{ $t(item.desc, { value: item.value }) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
