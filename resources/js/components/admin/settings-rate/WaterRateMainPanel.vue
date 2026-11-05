<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Icon } from '@iconify/vue';
import { WaterRateListPanel } from '.';

defineProps<{
    latest_rate: Array<any>;
}>();
</script>

<template>
    <Card class="gap-2 space-y-2 py-5 shadow-none">
        <div class="flex items-center justify-between gap-2 px-5">
            <div class="space-y-1">
                <h3 class="fluid-text-sm-base text-primary-text font-semibold">{{ $t('page.rate_settings.label.latest_rates') }}</h3>
                <p class="text-secondary-text fluid-text-xs-sm">{{ $t('page.rate_settings.label.latest_rates_subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button size="sm" @click="$emit('openAddModal')">
                    <Icon icon="lucide:plus" class="size-4" />
                    <span class="hidden md:flex">{{ $t('action.add_rate') }}</span>
                </Button>
            </div>
        </div>

        <WaterRateListPanel
            :latest_rate
            @openUpdateModal="(rate: any) => $emit('openUpdateModal', rate)"
            @openDeleteModal="(rate: any) => $emit('openDeleteModal', rate)"
        />
    </Card>
</template>
