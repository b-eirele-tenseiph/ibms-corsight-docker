<script setup lang="ts">
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Icon } from '@iconify/vue';
import { useAttrs } from 'vue';

const attrs = useAttrs();

const tabs: { value: string; icon: string }[] = [
    { value: 'monitoring', icon: 'lucide:activity' },
    { value: 'energy', icon: 'lucide:gauge' },
    { value: 'cctv', icon: 'lucide:scan-face' },
];
</script>

<template>
    <Tabs v-bind="attrs" default-value="monitoring" class="px-2 py-2">
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
</template>
