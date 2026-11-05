<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import { Rate, RateHistory } from '@/types/t-general-settings';
import { ElectricityCurrentRate, ElectricityRateAddPopover, ElectricityRateShowPopover } from '.';

const showAddModal = defineModel<boolean>('showAddModal', { required: true });
const showViewRateModal = defineModel<boolean>('showViewRateModal', { required: true });

defineProps<{
    rates: Array<RateHistory>;
    latest_rate: Rate;
}>();
</script>

<template>
    <Card class="gap-2 space-y-2 py-5 shadow-none">
        <div class="flex items-center justify-between gap-2 px-5">
            <div class="space-y-1">
                <h3 class="fluid-text-sm-base text-primary-text font-semibold">{{ $t('page.rate_settings.label.current_rate') }}</h3>
                <p class="text-secondary-text fluid-text-xs-sm">{{ $t('page.rate_settings.label.current_rate_subtitle') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Add Rate Popover -->
                <ElectricityRateAddPopover v-model:show="showAddModal" />

                <!-- View History Popover -->
                <ElectricityRateShowPopover v-model:show="showViewRateModal" :rateHistory="rates" />
            </div>
        </div>

        <!-- Current Rate Display -->
        <ElectricityCurrentRate :currentRate="latest_rate" />
    </Card>
</template>
