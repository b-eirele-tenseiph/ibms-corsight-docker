<script setup lang="ts">
import { StateEmpty } from '@/components/shared/states';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { waterRateData } from '@/routes/admin/water-billing-report';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';

const tabs = ref([
    {
        icon: 'lucide:home',
        value: 'residential',
    },
    {
        icon: 'lucide:building-2',
        value: 'commercial',
    },
    {
        icon: 'lucide:factory',
        value: 'industrial',
    },
]);

const rates = ref<Array<any>>([]);
const isLoading = ref<boolean>(true);
const activeTab = ref<string>('residential');

const loadData = (consumerType: string) => {
    isLoading.value = true;
    axios
        .get(waterRateData({ query: { class: consumerType } }).url)
        .then((response) => {
            rates.value = response.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

watch(activeTab, (newTab) => {
    loadData(newTab);
});

onMounted(() => {
    loadData(activeTab.value);
});
</script>

<template>
    <Card class="h-full gap-4 py-4">
        <CardHeader>
            <div class="flex w-full flex-col">
                <span class="fluid-text-sm-base text-primary-text font-bold">
                    {{ $t('page.water_billing_report.header.rate_structure') }}
                </span>
                <span class="fluid-text-xs-sm text-secondary-text font-semibold">
                    {{ $t('page.water_billing_report.header.rate_structure_subtitle') }}
                </span>
            </div>
        </CardHeader>
        <CardContent class="px-2">
            <Tabs v-model="activeTab" defaultValue="residential" class="w-full gap-4">
                <TabsList class="grid w-full grid-cols-3 px-4">
                    <TabsTrigger
                        v-for="tab in tabs"
                        :key="tab.value"
                        :value="tab.value"
                        class="data-[state=active]:bg-accent/60 fluid-text-xs-sm flex flex-col items-center justify-center gap-1 leading-tight capitalize data-[state=active]:shadow-none sm:flex-row sm:gap-2"
                    >
                        <Icon :icon="tab.icon" class="size-3.5 shrink-0" />
                        <span class="truncate">{{ tab.value }}</span>
                    </TabsTrigger>
                </TabsList>

                <div class="h-80">
                    <TabsContent v-for="tab in tabs" :key="tab.value" :value="tab.value" class="h-full overflow-hidden py-2">
                        <ScrollArea class="h-full">
                            <div class="px-4">
                                <template v-if="rates && rates.length > 0">
                                    <div v-for="(rateRecord, rateIndex) in rates" :key="rateIndex" class="space-y-3">
                                        <div
                                            v-for="meterRate in rateRecord?.rates || []"
                                            :key="meterRate.id"
                                            class="bg-card rounded-lg border p-3 shadow-xs"
                                        >
                                            <!-- Meter Size Header -->
                                            <div class="fluid-text-xs-sm mb-2 flex items-center justify-between border-b pb-2">
                                                <span class="text-muted-foreground font-semibold">
                                                    {{ $t('table.header.meter_size') }}
                                                </span>
                                                <span class="bg-accent/60 rounded-sm px-2 py-1 font-bold"> {{ meterRate.meter_size }}" </span>
                                            </div>

                                            <!-- Tiers -->
                                            <div
                                                v-for="(tier, index) in meterRate.tiers"
                                                :key="`${meterRate.id}-${index}`"
                                                class="flex items-center justify-between p-2.5"
                                            >
                                                <div class="flex-1 space-y-1">
                                                    <div class="text-secondary-text text-[10px] font-medium uppercase">
                                                        {{ $t('table.header.tiers') }}
                                                    </div>
                                                    <div class="text-xs font-semibold tabular-nums">
                                                        {{ tier.min_cubic_meters }} - {{ tier.max_cubic_meters }} m³
                                                    </div>
                                                </div>
                                                <div class="space-y-1 text-right">
                                                    <div class="text-secondary-text text-[10px] font-medium uppercase">
                                                        {{ $t('table.header.rate_per_m3') }}
                                                    </div>
                                                    <div class="text-primary-text text-sm font-bold tabular-nums">
                                                        ₱{{ tier.rate_per_cubic_meter.toFixed(2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State for Mobile -->
                                <div v-else class="flex h-full items-center justify-center py-8">
                                    <StateEmpty />
                                </div>
                            </div>
                        </ScrollArea>
                    </TabsContent>
                </div>
            </Tabs>
        </CardContent>
    </Card>
</template>
