<script setup lang="ts">
import { Rate } from '@/types/t-general-settings';
import dayjs from 'dayjs';
import { RateEmptyState } from '.';

defineProps<{
    currentRate: Rate;
}>();
</script>

<template>
    <div v-if="currentRate?.effective_date" class="rounded-lg px-4 py-2">
        <div class="flex items-center justify-between">
            <div class="gap-1">
                <p class="text-secondary-text fluid-text-xs-sm">{{ $t('form.label.month') }}</p>
                <p class="text-primary-text fluid-text-sm-base font-semibold">
                    {{ dayjs(currentRate.effective_date).format('MMMM YYYY') }}
                </p>
            </div>
            <div class="gap-1 text-right">
                <p class="text-secondary-text fluid-text-xs-sm">{{ $t('form.label.rate_per_kwh') }}</p>
                <p class="text-primary-text fluid-text-base-lg font-bold tabular-nums">
                    {{ `₱${Number(currentRate.rate_per_kwh).toFixed(4)}` }}
                </p>
            </div>
        </div>
    </div>

    <RateEmptyState
        v-else
        icon="mage:light-bulb-electricity"
        title="page.rate_settings.state.no_electricity_rate"
        description="page.rate_settings.state.no_electricity_rate_description"
    />
</template>
