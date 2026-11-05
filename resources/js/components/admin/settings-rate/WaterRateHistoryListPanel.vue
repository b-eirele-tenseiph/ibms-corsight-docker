<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';

defineProps<{
    rate: any;
}>();

const formatConsumptionRange = (min: number, max: number | null) => {
    if (max === null) {
        return `${min}+ m³`;
    }
    return `${min} - ${max} m³`;
};

const formatDate = (date: Date) => {
    return dayjs(date).format('MMMM YYYY');
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 px-4">
        <Label class="fluid-text-xs-sm font-semibold">
            {{ $t('page.rate_settings.label.rate_history') }}
        </Label>
        <Badge variant="secondary" class="font-semibold">
            {{ rate.rates?.length || 0 }}
            <Icon icon="mdi:database-outline" />
        </Badge>
    </div>

    <div class="h-60 overflow-hidden">
        <ScrollArea class="h-full">
            <div class="space-y-2 px-4 py-4 sm:space-y-4">
                <Card v-for="record in rate.rates" :key="record.id" class="border py-2">
                    <CardContent class="p-4">
                        <div class="mb-3 flex items-start justify-between gap-2 border-b pb-3">
                            <div class="flex-1">
                                <Label class="text-xs">
                                    {{ $t('table.header.effective_date') }}
                                </Label>
                                <div class="mt-1 text-sm font-semibold">
                                    {{ formatDate(record.effective_date) }}
                                </div>
                            </div>
                            <DropdownAction
                                :actions="{
                                    update: { label: 'modal.action.update_rate' },
                                    delete: { label: 'modal.action.delete_rate' },
                                }"
                                @update="
                                    $emit('openUpdateModal', {
                                        ...record,
                                        meter_size: rate.meter_size,
                                        consumer_type: rate.consumer_type,
                                    })
                                "
                                @delete="$emit('openDeleteModal', { ...record, meter_size: rate.meter_size, consumer_type: rate.consumer_type })"
                            />
                        </div>

                        <!-- Tiers and charges -->
                        <div class="space-y-2">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold">
                                <span class="flex-1">{{ $t('table.header.tiers') }}</span>
                                <span>{{ $t('table.header.charges') }}</span>
                            </div>

                            <div
                                v-for="(tier, tierIndex) in record.tiers"
                                :key="tierIndex"
                                class="bg-background hover:bg-accent/50 flex items-center justify-between rounded-md p-3 text-xs transition-colors"
                            >
                                <span class="flex-1 tabular-nums">
                                    {{ formatConsumptionRange(tier.min_cubic_meters, tier.max_cubic_meters) }}
                                </span>
                                <span class="text-right text-sm font-bold tabular-nums"> ₱{{ tier.rate_per_cubic_meter.toFixed(2) }} </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </ScrollArea>
    </div>
</template>
