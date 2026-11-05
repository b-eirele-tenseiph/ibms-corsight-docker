<script lang="ts" setup>
import { StateLoading } from '@/components/shared/states';
import StateError from '@/components/shared/states/StateError.vue';
import SwitchBotSyncModal from '@/components/shared/switch-bot-sync/SwitchBotSyncModal.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { store, sync } from '@/routes/admin/management/switchbot/remotes';
import { SwitchBotRemoteSyncData } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const accordionValues = ref<string[]>([]);
const { isTinyMobile, isExtraSmallMobile, isSmallMobile, isMobile } = useBreakpoints();
const smallScreen = computed(() => isTinyMobile.value || isExtraSmallMobile.value || isSmallMobile.value || isMobile.value);

const loading = ref(false);
const applying = ref(false);
const devices = ref<SwitchBotRemoteSyncData[]>([]);
const modalOpen = ref(false);
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
        // Filter devices by state and extract only the required data
        const newDevices = devices.value
            .filter((d) => d.status === 'new')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                type_id: d.type?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        const updatedDevices = devices.value
            .filter((d) => d.status === 'updated')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                type_id: d.type?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        const outdatedDevices = devices.value
            .filter((d) => d.status === 'outdated')
            .map((d) => ({
                id: d.id,
                name: d.name,
                serial_number: d.serial_number,
                type_id: d.type?.id ?? null,
                hub_id: d.hub?.id ?? null,
            }));

        // Send only the required data for each state group
        await axios.post(store.url(), {
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
</script>

<template>
    <SwitchBotSyncModal
        v-model:open="modalOpen"
        @scanDevices="scanDevices"
        @applyChanges="applyChanges"
        :loading
        :applying
        :header="'switch_bot_remote_scan'"
        :title="'switch_bot_remote_scan_result'"
        :description="'switch_bot_remote_scan_result'"
        :failedAccounts="failedAccounts"
    >
        <div v-if="!loading" class="scan-modal-table-container max-h-72">
            <Table class="scan-modal-table-main">
                <!-- Table Header -->
                <TableHeader class="scan-modal-table-header fluid-text-xs-sm">
                    <template v-for="(device, index) in devices" :key="index">
                        <TableRow class="scan-modal-table-header-row">
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.device_name') }}</TableHead>
                            <TableHead v-if="!smallScreen" class="h-12 w-1/20 py-3">{{ $t('table.header.room_name') }}</TableHead>
                            <TableHead class="h-12 w-1/20 py-3">{{ $t('table.header.serial_num') }}</TableHead>
                        </TableRow>
                    </template>
                </TableHeader>
            </Table>

            <!-- Expandable Accordion Table Body -->
            <Accordion type="single" collapsible v-model="accordionValues" class="w-full">
                <Table class="scan-modal-table-main">
                    <TableBody class="table-body" v-if="devices.length">
                        <AccordionItem v-for="device in devices" :key="device.id" :value="String(device.id)" as="template">
                            <TableRow
                                class="scan-modal-table-body-row"
                                :class="[
                                    device.status === 'new' && 'bg-sync-modal-label-green',
                                    device.status === 'outdated' && 'bg-sync-modal-label-red',
                                    device.status === 'updated' && 'bg-sync-modal-label-blue',
                                ]"
                            >
                                <TableCell class="xs:w-1/2 w-1/2 p-1 sm:w-1/20 sm:p-2">
                                    <AccordionTrigger class="fluid-text-xs-sm h-14 py-3 whitespace-break-spaces hover:no-underline sm:h-12 sm:py-3">
                                        <div class="w-full">
                                            <div>
                                                <span>
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

                                <TableCell v-if="!smallScreen" class="w-1/20 truncate p-1 sm:p-2">
                                    <AccordionTrigger :key="device.id" class="fluid-text-xs-sm h-9 py-2 hover:no-underline sm:h-12 sm:py-3">
                                        <span>{{ device.room?.name || '-' }}</span>
                                    </AccordionTrigger>
                                </TableCell>

                                <TableCell class="w-1/2 p-1 whitespace-pre-line sm:w-1/20 sm:p-2">
                                    <AccordionTrigger
                                        :key="device.id"
                                        class="fluid-text-xs-sm h-14 gap-1 py-3 text-clip hover:no-underline sm:h-12 sm:py-3"
                                    >
                                        <span :class="[smallScreen ? 'w-1/20 place-self-center' : 'w-auto']">{{ device.serial_number }}</span>
                                        <Icon class="place-self-center" v-if="smallScreen" icon="lucide:chevron-down" />
                                    </AccordionTrigger>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="smallScreen">
                                <TableCell colspan="4">
                                    <AccordionContent class="fluid-text-xs-sm p-3">
                                        <div class="py-2">
                                            <span class="font-bold">{{ $t('table.header.room_name') }}: </span>{{ device.room?.name || '-' }}
                                        </div>
                                        <div class="py-2">
                                            <span class="font-bold">{{ $t('table.header.hub_name') }}: </span>{{ device.hub?.name }}
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

        <!-- Loader -->
        <StateLoading :loading />
    </SwitchBotSyncModal>
</template>
