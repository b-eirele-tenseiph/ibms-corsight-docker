<script setup lang="ts">
import { ClimateTable } from '@/components/admin/monitoring-climate';
import { PlugTable } from '@/components/admin/monitoring-plug';
import { SwitchTable } from '@/components/admin/monitoring-switch';
import Paginate from '@/components/shared/pagination/Paginate.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AdminPageLayout } from '@/layouts/admin';
import { devices } from '@/routes/admin/dashboard';
import { DeviceTabType, LorawanSwitch, PaginatedResponse, QueryParams, SwitchBotDeviceData, SwitchBotRemoteData } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { DevicesSearch } from '.';

const props = defineProps<{
    roomId: number | string;
    queryParams: QueryParams;
    activeTab: DeviceTabType;
    aircons: PaginatedResponse<SwitchBotRemoteData>;
    switches: PaginatedResponse<LorawanSwitch>;
    appliances: PaginatedResponse<SwitchBotDeviceData>;
}>();

const activeTab = ref<DeviceTabType>(props.activeTab);
const isLoading = ref(false);

const tabConfig = {
    aircon: { prop: 'aircons', label: 'navigation.admin.climate' },
    switches: { prop: 'switches', label: 'navigation.admin.switches' },
    appliances: { prop: 'appliances', label: 'navigation.admin.plugs' },
} as const;

const activePagination = computed(() => {
    const data = props[tabConfig[activeTab.value].prop];
    return data?.data ? data : null;
});

const switchTab = (tab: string | number) => {
    if (activeTab.value === tab) return;

    activeTab.value = tab as DeviceTabType;
    isLoading.value = true;

    router.get(
        devices({ query: { room: props.roomId, tab } }).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['aircons', 'switches', 'appliances', 'activeTab'],
            onFinish: () => (isLoading.value = false),
        },
    );
};
</script>

<template>
    <AdminPageLayout class="mt-0 flex h-[50vh] flex-col">
        <Tabs :model-value="activeTab" @update:model-value="switchTab" class="flex flex-1 flex-col overflow-hidden">
            <TabsList class="flex h-auto w-full flex-col-reverse items-start gap-4 bg-transparent p-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-2">
                    <TabsTrigger
                        v-for="(config, key) in tabConfig"
                        :key="key"
                        :value="key"
                        :disabled="isLoading"
                        class="data-[state=active]:after:bg-primary fluid-text-xs-sm data-[state=active]:text-primary relative rounded-none pb-2 font-semibold after:absolute after:right-0 after:bottom-0 after:left-0 after:h-0.5 data-[state=active]:bg-transparent data-[state=active]:shadow-none"
                    >
                        {{ $t(config.label) }}
                    </TabsTrigger>
                </div>
                <DevicesSearch :filters="props.queryParams" :activeTab="activeTab" />
            </TabsList>
            <div class="mt-2 min-h-0 flex-1">
                <div v-if="isLoading" class="flex h-full items-center justify-center">
                    <Icon icon="eos-icons:three-dots-loading" class="text-primary size-16" />
                </div>
                <template v-else>
                    <TabsContent value="aircon" class="h-full overflow-y-auto">
                        <ClimateTable :aircons="aircons.data" :queryParams="queryParams" />
                    </TabsContent>
                    <TabsContent value="switches" class="h-full overflow-y-auto">
                        <SwitchTable :switches="switches.data" :queryParams="queryParams" />
                    </TabsContent>
                    <TabsContent value="appliances" class="h-full overflow-y-auto">
                        <PlugTable :appliances="appliances.data" :queryParams="queryParams" />
                    </TabsContent>
                </template>
            </div>
        </Tabs>
        <Paginate v-if="activePagination" class="flex-shrink-0" :paginate="activePagination" />
    </AdminPageLayout>
</template>
