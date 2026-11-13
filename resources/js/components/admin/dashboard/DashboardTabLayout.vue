<script setup lang="ts">
import { CctvToolbar } from '@/components/admin/dashboard';
import type { CorsightMonitoring } from '@/composables/useCorsightMonitoring';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Icon } from '@iconify/vue';
import { computed, useAttrs } from 'vue';

const props = defineProps<{
    corsight: CorsightMonitoring;
}>();

const attrs = useAttrs();

// The toolbar is CCTV-specific; it lives on the tab row so it stays aligned
// with the tab switcher, and only renders on the CCTV tab.
const { isLive, lastUpdated, rooms, devices, selectedLocation, selectedDevices, selectedRange } = props.corsight;
const activeTab = computed(() => String(attrs.modelValue ?? 'monitoring'));

const tabs: { value: string; icon: string }[] = [
    { value: 'monitoring', icon: 'lucide:activity' },
    { value: 'energy', icon: 'lucide:gauge' },
    { value: 'cctv', icon: 'lucide:scan-face' },
];
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 px-2 py-2">
        <div class="shrink-0">
            <Tabs v-bind="attrs" default-value="monitoring">
                <TabsList class="bg-accent/70 shadow-xs">
                    <TabsTrigger
                        v-for="tab in tabs"
                        :key="tab.value"
                        :value="tab.value"
                        class="data-[state=active]:bg-background flex items-center px-3 py-1.5 data-[state=active]:shadow-sm"
                    >
                        <Icon :icon="tab.icon" class="size-3.5" />
                        <span class="fluid-text-xs-sm font-semibold">{{ $t(`label.${tab.value}`) }}</span>
                    </TabsTrigger>
                </TabsList>
            </Tabs>
        </div>

        <div class="flex-1" />

        <CctvToolbar
            v-if="activeTab === 'cctv'"
            v-model:selected-location="selectedLocation"
            v-model:selected-devices="selectedDevices"
            v-model:selected-range="selectedRange"
            :is-live="isLive"
            :last-updated="lastUpdated"
            :rooms="rooms"
            :devices="devices"
        />
    </div>
</template>
