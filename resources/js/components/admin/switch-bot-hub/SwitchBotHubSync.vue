<script lang="ts" setup>
import { StateError, StateLoading } from '@/components/shared/states';
import SwitchBotSyncModal from '@/components/shared/switch-bot-sync/SwitchBotSyncModal.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { sync, syncStore } from '@/routes/admin/management/switchbot/hubs';
import { SwitchBotHubData } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Define your device type (edit as needed)
const { isTinyMobile, isExtraSmallMobile, isSmallMobile, isMobile } = useBreakpoints();
const smallScreen = computed(() => isTinyMobile.value || isExtraSmallMobile.value || isSmallMobile.value || isMobile.value);
const accordionValues = ref<string[]>([]);
const loading = ref<boolean>(false);
const applying = ref<boolean>(false);
const hubs = ref<SwitchBotHubData[]>();
const modalOpen = ref(false);
const loadingTitle = ref<string>('');
const failedAccounts = ref<any[]>([]);

// Load data from backend and tag each with a status
async function scanDevices() {
    loading.value = true;
    failedAccounts.value = [];
    try {
        const { data } = await axios.get(sync.url());
        // Merge and tag
        hubs.value = [
            ...(data.new ?? []).map((hub: any) => ({ ...hub, status: 'new' })),
            ...(data.updated ?? []).map((hub: any) => ({ ...hub, status: 'updated' })),
            ...(data.outdated ?? []).map((hub: any) => ({ ...hub, status: 'outdated' })),
        ];
        failedAccounts.value = data.failed ?? [];
    } finally {
        loading.value = false;
    }
}

async function applyChanges() {
    applying.value = true;
    try {
        // Filter devices by status and extract only the required data
        const newHubs = (hubs.value ?? [])
            .filter((hub) => hub.status === 'new')
            .map((hub) => ({
                id: hub.id,
                name: hub.name,
                serial_number: hub.serial_number,
                type_id: hub.type_id ?? null,
                room_id: hub.room?.id ?? null,
                room_area_id: hub.room_area?.id ?? null,
            }));

        const updatedHubs = (hubs.value ?? [])
            .filter((hub) => hub.status === 'updated')
            .map((hub) => ({
                id: hub.id,
                name: hub.name,
                serial_number: hub.serial_number,
                type_id: hub.type_id ?? null,
                room_id: hub.room?.id ?? null,
                room_area_id: hub.room_area?.id ?? null,
            }));

        const outdatedHubs = (hubs.value ?? [])
            .filter((hub) => hub.status === 'outdated')
            .map((hub) => ({
                id: hub.id,
                name: hub.name,
                serial_number: hub.serial_number,
                type_id: hub.type_id ?? null,
                room_id: hub.room?.id ?? null,
                room_area_id: hub.room_area?.id ?? null,
            }));

        // Send only the required data for each status group
        await axios.post(syncStore.url(), {
            new: newHubs,
            updated: updatedHubs,
            outdated: outdatedHubs,
        });
        modalOpen.value = false;
        router.reload({ preserveScroll: true });
    } finally {
        applying.value = false;
    }
}

const { stopListening: stopDeviceSync, listen: listenDeviceSync } = useEchoPublic(`admin`, '.switchbot.hub.resources.syncing', (data: any) => {
    loadingTitle.value = data.message;
});

onMounted(() => {
    listenDeviceSync();
});

onUnmounted(() => {
    stopDeviceSync();
});
</script>

<template>
    <SwitchBotSyncModal
        :header="'switch_bot_hub_scan'"
        :title="'switch_bot_hub_scan_result'"
        :description="'switch_bot_hub_scan_result'"
        v-model:open="modalOpen"
        @scanDevices="scanDevices"
        @applyChanges="applyChanges"
        :loading
        :applying
        :failedAccounts="failedAccounts"
    >
        <!-- Table -->
        <div v-if="!loading" class="scan-modal-table-container max-h-72">
            <Table class="scan-modal-table-main">
                <TableHeader class="scan-modal-table-header fluid-text-xs-sm">
                    <template v-for="(hub, index) in hubs" :key="index">
                        <TableRow class="scan-modal-table-header-row">
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.device_name') }}</TableHead>
                            <TableHead v-if="!smallScreen" class="h-12 w-1/20 py-3">{{ $t('table.header.room_name') }}</TableHead>
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.serial_num') }}</TableHead>
                        </TableRow>
                    </template>
                </TableHeader>
            </Table>

            <!-- EXP -->
            <Accordion type="single" v-model="accordionValues" class="w-full" collapsible>
                <Table class="scan-modal-table-main">
                    <TableBody class="table-body" v-if="hubs?.length">
                        <AccordionItem v-for="hub in hubs" :key="hub.id" :value="hub.id ? String(hub.id) : ''" as="template">
                            <TableRow
                                class="scan-modal-table-body-row"
                                :class="[
                                    hub.status === 'new' && 'bg-sync-modal-label-green',
                                    hub.status === 'outdated' && 'bg-sync-modal-label-red',
                                    hub.status === 'updated' && 'bg-sync-modal-label-blue',
                                ]"
                            >
                                <TableCell class="xs:w-1/2 w-1/2 sm:w-1/20">
                                    <AccordionTrigger :key="hub.id" class="fluid-text-xs-sm h-9 w-full py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <div class="w-full">
                                            <div>
                                                <span class="block w-full truncate">
                                                    {{ hub.name }}
                                                </span>
                                            </div>
                                            <div
                                                v-if="hub.name !== hub.switchbot_hub && hub.switchbot_hub && !smallScreen"
                                                class="text-xs text-gray-500"
                                            >
                                                {{ $t('table.header.name_on_app') }}: {{ hub.switchbot_hub }}
                                            </div>
                                        </div>
                                    </AccordionTrigger>
                                </TableCell>
                                <TableCell v-if="!smallScreen" class="w-1/20 truncate">
                                    <AccordionTrigger :key="hub.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ hub.room?.name || '-' }}</span>
                                    </AccordionTrigger>
                                </TableCell>
                                <TableCell class="w-1/2 truncate sm:w-1/20">
                                    <AccordionTrigger :key="hub.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ hub.serial_number }}</span>
                                        <Icon v-if="smallScreen" icon="lucide:chevron-down" />
                                    </AccordionTrigger>
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-if="smallScreen"
                                class="scan-modal-table-body-row"
                                :class="[
                                    hub.status === 'new' && 'bg-sync-modal-label-green',
                                    hub.status === 'outdated' && 'bg-sync-modal-label-red',
                                    hub.status === 'updated' && 'bg-sync-modal-label-blue',
                                ]"
                            >
                                <TableCell colspan="4" class="p-0">
                                    <AccordionContent class="fluid-text-xs-sm p-2">
                                        <div v-if="hub.name !== hub.switchbot_hub && hub.switchbot_hub" class="py-2">
                                            <span class="font-bold">{{ $t('table.header.name_on_app') }}: </span>{{ hub.switchbot_hub || '' }}
                                        </div>
                                        <div class="py-2">
                                            <span class="font-bold">{{ $t('table.header.room_name') }}: </span>{{ hub.room?.name || '' }}
                                        </div>
                                    </AccordionContent>
                                </TableCell>
                            </TableRow>
                        </AccordionItem>
                    </TableBody>
                    <TableBody v-else class="bg-background">
                        <StateError :message="$t('state.error')" />
                    </TableBody>
                </Table>
            </Accordion>
        </div>
        <!-- StateLoading -->
        <StateLoading :loading :title="loadingTitle" />
    </SwitchBotSyncModal>
</template>
