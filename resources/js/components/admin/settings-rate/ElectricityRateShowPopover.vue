<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { RateHistory } from '@/types/t-general-settings';
import { Icon } from '@iconify/vue';
import { ElectricityRateShowContent, ElectricityRateShowEmptyState } from '.';

defineProps<{
    show: boolean;
    rateHistory: Array<RateHistory>;
}>();

const emit = defineEmits<{
    'update:show': [value: boolean];
}>();
</script>

<template>
    <Popover :open="show" @update:open="emit('update:show', $event)">
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm">
                <Icon icon="lucide:history" />
                <span class="hidden md:flex">{{ $t('page.rate_settings.label.view_rate_history') }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="z-50 w-80" align="end" :side-offset="5">
            <div class="space-y-4">
                <div class="space-y-1">
                    <h4 class="fluid-text-xs-sm text-primary-text font-semibold">{{ $t('page.rate_settings.label.rate_history') }}</h4>
                    <p class="text-secondary-text text-xs">{{ $t('page.rate_settings.label.rate_history_subtitle') }}</p>
                </div>

                <ScrollArea :class="rateHistory.length > 5 ? 'h-80' : 'h-auto'">
                    <ElectricityRateShowContent v-if="rateHistory.length > 0" :rateHistory="rateHistory" />

                    <ElectricityRateShowEmptyState v-else />
                </ScrollArea>
            </div>
        </PopoverContent>
    </Popover>
</template>
