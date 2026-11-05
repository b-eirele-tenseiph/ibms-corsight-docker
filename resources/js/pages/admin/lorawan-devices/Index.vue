<script setup lang="ts">
import { AddModal, ConfigureModal, DeleteModal, Filter, Table, UpdateModal } from '@/components/admin/lorawan-devices';
import LorawanDevicesActions from '@/components/admin/lorawan-devices/LorawanDevicesActions.vue';
import { Paginate } from '@/components/shared/pagination';
import { Button } from '@/components/ui/button';
import { useModal } from '@/composables/useModal';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { AdminPageLayout } from '@/layouts/admin';
import type { LorawanDeviceData, LorawanDeviceProps } from '@/types';
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';

defineProps<LorawanDeviceProps>();

const ADD_MODAL = useModal();
const UPDATE_MODAL = useModal<LorawanDeviceData>();
const DELETE_MODAL = useModal<LorawanDeviceData>();
const CONFIGURE_MODAL = useModal<LorawanDeviceData>();
</script>

<template>
    <Head :title="$t('head.lorawan_devices')" />
    <AdminLayout title="head.lorawan_devices">
        <AdminPageLayout>
            <!-- Header -->
            <div class="flex flex-row justify-between gap-4">
                <div class="xs:flex-row flex w-full flex-col gap-2">
                    <Filter :filters :rooms />
                </div>
                <div class="flex flex-row justify-end gap-2">
                    <Button variant="default" @click="ADD_MODAL.open" class="cursor-pointer">
                        <Icon icon="lucide:plus" class="size-4" />
                        <span class="hidden md:flex">{{ $t('modal.action.add_devices') }}</span>
                    </Button>
                    <LorawanDevicesActions :operations="['template', 'import', 'export']" />
                </div>
            </div>

            <Table
                :queryParams="filters"
                :devices="devices.data"
                @update="UPDATE_MODAL.open"
                @delete="DELETE_MODAL.open"
                @configure="CONFIGURE_MODAL.open"
            />

            <Paginate :paginate="devices" />

            <!-- Add Modal -->
            <AddModal
                :isOpen="ADD_MODAL.visible.value"
                @close="ADD_MODAL.close"
                :manufacturers="manufacturers"
                :gateways="gateways"
                :rooms="rooms"
                :roomAreas="roomAreas"
                :models="models"
                :floors="floors"
            />

            <!-- Update Modal -->
            <UpdateModal
                v-if="UPDATE_MODAL.visible.value && UPDATE_MODAL.data.value"
                :isOpen="UPDATE_MODAL.visible.value"
                :deviceData="UPDATE_MODAL.data.value"
                @close="UPDATE_MODAL.close"
                :manufacturers="manufacturers"
                :gateways="gateways"
                :rooms="rooms"
                :roomAreas="roomAreas"
                :floors="floors"
            />

            <!-- Delete Modal -->
            <DeleteModal
                v-if="DELETE_MODAL.visible.value && DELETE_MODAL.data.value"
                :isOpen="DELETE_MODAL.visible.value"
                :device="DELETE_MODAL.data.value"
                @close="DELETE_MODAL.close"
            />

            <!-- Configure Modal -->
            <ConfigureModal :isOpen="CONFIGURE_MODAL.visible.value" :device="CONFIGURE_MODAL.data.value!" @close="CONFIGURE_MODAL.close" />
        </AdminPageLayout>
    </AdminLayout>
</template>
