<script lang="ts" setup>
import { StateError, StateLoading } from '@/components/shared/states';
import { SwitchBotSyncModal } from '@/components/shared/switch-bot-sync';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { sync, syncStore } from '@/routes/admin/management/switchbot/devices';
import { SwitchBotDeviceSyncData } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const { isTinyMobile, isExtraSmallMobile, isSmallMobile, isMobile } = useBreakpoints();
const smallScreen = computed(() => isTinyMobile.value || isExtraSmallMobile.value || isSmallMobile.value || isMobile.value);
const accordionValues = ref<string[]>([]);
const loading = ref<boolean>(false);
const applying = ref<boolean>(false);
const devices = ref<SwitchBotDeviceSyncData[]>([]);
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
        devices.value = [
            ...(data.new ?? []).map((d: any) => ({ ...d, status: 'new' })),
            ...(data.updated ?? []).map((d: any) => ({ ...d, status: 'updated' })),
            ...(data.outdated ?? []).map((d: any) => ({ ...d, status: 'outdated' })),
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
        const newDevices = devices.value
            .filter((d) => d.status === 'new')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                context: d.context,
                type_id: d.type?.id ?? null,
                room_id: d.room?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        const updatedDevices = devices.value
            .filter((d) => d.status === 'updated')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                context: d.context,
                type_id: d.type?.id ?? null,
                room_id: d.room?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        const outdatedDevices = devices.value
            .filter((d) => d.status === 'outdated')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                context: d.context,
                type_id: d.type?.id ?? null,
                room_id: d.room?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        // Send only the required data for each status group
        await axios.post(syncStore.url(), {
            new: newDevices,
            updated: updatedDevices,
            outdated: outdatedDevices,
        });
        modalOpen.value = false;
        router.reload({ preserveScroll: true });
    } finally {
        applying.value = false;
    }
}

const { stopListening: stopDeviceSync, listen: listenDeviceSync } = useEchoPublic(`admin`, '.switchbot.device.resources.syncing', (data: any) => {
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
        :header="'switch_bot_device_scan'"
        :title="'switch_bot_device_scan_result'"
        :description="'switch_bot_device_scan_result'"
        v-model:open="modalOpen"
        @scanDevices="scanDevices"
        @applyChanges="applyChanges"
        :failedAccounts="failedAccounts"
        :loading
        :applying
    >
        <!-- Table -->
        <div v-if="!loading" class="scan-modal-table-container max-h-72">
            <Table class="scan-modal-table-main">
                <TableHeader class="scan-modal-table-header fluid-text-xs-sm">
                    <template v-for="(device, index) in devices" :key="index">
                        <TableRow class="scan-modal-table-header-row">
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.device_name') }}</TableHead>
                            <TableHead v-if="!smallScreen" class="h-12 w-1/20 py-3">{{ $t('table.header.room_name') }}</TableHead>
                            <TableHead v-if="!smallScreen" class="h-12 w-1/20 py-3">{{ $t('table.header.hub_name') }}</TableHead>
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.serial_num') }}</TableHead>
                        </TableRow>
                    </template>
                </TableHeader>
            </Table>

            <!-- EXP -->
            <Accordion type="single" v-model="accordionValues" class="w-full" collapsible>
                <Table class="scan-modal-table-main">
                    <TableBody class="table-body" v-if="devices?.length">
                        <AccordionItem v-for="device in devices" :key="device.id" :value="device.id ? String(device.id) : ''" as="template">
                            <TableRow
                                class="scan-modal-table-body-row"
                                :class="[
                                    device.status === 'new' && 'bg-sync-modal-label-green',
                                    device.status === 'outdated' && 'bg-sync-modal-label-red',
                                    device.status === 'updated' && 'bg-sync-modal-label-blue',
                                ]"
                            >
                                <TableCell class="xs:w-1/2 w-1/2 sm:w-1/20">
                                    <AccordionTrigger :key="device.id" class="fluid-text-xs-sm h-9 w-full py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <div class="w-full">
                                            <div>
                                                <span class="block w-full truncate">
                                                    {{ device.name }}
                                                </span>
                                            </div>
                                            <div
                                                v-if="device.name !== device.switchbot_name && device.switchbot_name && !smallScreen"
                                                class="text-xs text-gray-500"
                                            >
                                                {{ $t('table.header.name_on_app') }}: {{ device.switchbot_name }}
                                            </div>
                                        </div>
                                    </AccordionTrigger>
                                </TableCell>
                                <TableCell v-if="!smallScreen" class="w-1/20 truncate">
                                    <AccordionTrigger :key="device.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ device.room?.name || '-' }}</span>
                                    </AccordionTrigger>
                                </TableCell>
                                <TableCell v-if="!smallScreen" class="w-1/20 truncate">
                                    <AccordionTrigger :key="device.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ device.hub?.name || '-' }}</span>
                                    </AccordionTrigger>
                                </TableCell>
                                <TableCell class="w-1/2 truncate sm:w-1/20">
                                    <AccordionTrigger :key="device.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ device.serial_number }}</span>
                                        <Icon v-if="smallScreen" icon="lucide:chevron-down" />
                                    </AccordionTrigger>
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-if="smallScreen"
                                class="scan-modal-table-body-row"
                                :class="[
                                    device.status === 'new' && 'bg-sync-modal-label-green',
                                    device.status === 'outdated' && 'bg-sync-modal-label-red',
                                    device.status === 'updated' && 'bg-sync-modal-label-blue',
                                ]"
                            >
                                <TableCell colspan="4" class="p-0">
                                    <AccordionContent class="fluid-text-xs-sm p-2">
                                        <div v-if="device.name !== device.switchbot_name && device.switchbot_name" class="py-2">
                                            <span class="font-bold">{{ $t('table.header.name_on_app') }}: </span>{{ device.switchbot_name || '' }}
                                        </div>
                                        <div class="py-2">
                                            <span class="font-bold">{{ $t('table.header.room_name') }}: </span>{{ device.room?.name || '' }}
                                        </div>
                                        <div class="py-2">
                                            <span class="font-bold">{{ $t('table.header.hub_name') }}: </span>{{ device.hub?.name || '' }}
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
