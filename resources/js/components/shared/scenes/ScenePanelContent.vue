<script setup lang="ts">
import { ScrollArea } from '@/components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const tabs = ['information', 'devices'];
const emit = defineEmits<{
    (e: 'devices-clicked'): void;
}>();
</script>
<template>
    <Tabs default-value="information" class="flex min-h-0 flex-1 flex-col">
        <TabsList class="grid w-full grid-cols-2 rounded-none border-b-2 px-6">
            <TabsTrigger
                v-for="tab in tabs"
                :key="tab"
                :value="tab"
                class="fluid-text-xs-sm data-[state=active]:text-primary rounded-none border-2 data-[state=active]:border-none data-[state=active]:shadow-none"
                @click="tab === 'devices' && emit('devices-clicked')"
            >
                {{ $t(`page.scene_management.tabs.${tab}`) }}
            </TabsTrigger>
        </TabsList>

        <!-- Information content -->
        <TabsContent value="information" class="flex flex-col overflow-hidden">
            <ScrollArea class="min-h-0 flex-1 p-6">
                <slot name="information" />
            </ScrollArea>
        </TabsContent>

        <!-- Device content -->
        <TabsContent value="devices" class="flex h-full flex-col overflow-hidden">
            <ScrollArea class="min-h-0 flex-1 p-6">
                <slot name="devices" />
            </ScrollArea>
        </TabsContent>
    </Tabs>
</template>
