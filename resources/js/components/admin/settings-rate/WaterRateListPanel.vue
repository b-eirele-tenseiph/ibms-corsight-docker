<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Icon } from '@iconify/vue';
import { RateEmptyState, WaterRateHistoryListPanel } from '.';

defineProps<{
    latest_rate: Array<any>;
}>();

const consumerBadgeColor = (type: string) => {
    const colors = {
        residential: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800',
        commercial: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800',
        industrial: 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950 dark:text-violet-300 dark:border-violet-800',
    };
    return colors[type as keyof typeof colors] || colors.residential;
};

const consumerTypeIcon = (rate: string) => {
    const iconMap = {
        residential: 'lucide:home',
        commercial: 'lucide:store',
        industrial: 'lucide:factory',
    };

    return iconMap[rate as keyof typeof iconMap] || iconMap.residential;
};
</script>

<template>
    <div class="h-full w-full">
        <ScrollArea class="h-full w-full">
            <div class="p-4 sm:p-6">
                <Accordion v-if="latest_rate.length > 0" type="multiple">
                    <AccordionItem v-for="rate in latest_rate" :key="rate.id" :value="rate.id">
                        <AccordionTrigger class="data-[state=open]:bg-accent/50 hover:bg-accent/30 p-4 hover:no-underline">
                            <div class="flex w-full items-center justify-between gap-3">
                                <div class="fluid-text-xs-sm flex gap-4">
                                    <!-- Consumer Type Badge -->
                                    <Badge
                                        variant="outline"
                                        class="shrink-0 text-xs font-medium capitalize sm:text-sm"
                                        :class="consumerBadgeColor(rate.consumer_type)"
                                    >
                                        <Icon :icon="consumerTypeIcon(rate.consumer_type)" class="mr-1.5 h-3.5 w-3.5" />
                                        <span>{{ rate.consumer_type }}</span>
                                    </Badge>

                                    <!-- Meter Size Display -->
                                    <div class="flex shrink-0 items-center gap-1.5 text-sm">
                                        <Icon icon="lucide:gauge" class="size-4" />
                                        <span class="font-medium tabular-nums">{{ rate.meter_size }}"</span>
                                    </div>
                                </div>
                            </div>
                        </AccordionTrigger>

                        <!-- Accordion Content -->
                        <AccordionContent class="py-4">
                            <WaterRateHistoryListPanel
                                :rate="rate"
                                @openUpdateModal="$emit('openUpdateModal', $event)"
                                @openDeleteModal="$emit('openDeleteModal', $event)"
                            />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <RateEmptyState
                    v-else
                    icon="lucide:droplets"
                    title="page.rate_settings.state.no_water_rate"
                    description="page.rate_settings.state.no_water_rate_description"
                />
            </div>
        </ScrollArea>
    </div>
</template>
