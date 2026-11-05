<script setup lang="ts">
import {
    CorsightDeviceAddModal,
    CorsightDeviceConfigurationModal,
    CorsightDeviceDeleteModal,
    CorsightDeviceTable,
    CorsightDeviceTableActions,
    CorsightDeviceUpdateModal,
} from '@/components/admin/corsight-device';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { CorsightDeviceProps, type CorsightDeviceData } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<CorsightDeviceProps>();

const params = ref({ ...props.filters });

const modals = {
    add: useModal(),
    update: useModal<CorsightDeviceData>(),
    config: useModal<CorsightDeviceData>(),
    delete: useModal<Pick<CorsightDeviceData, 'id' | 'name'>>(),
};

const {
    add: { visible: showAddModal, open: openAddModal, close: closeAddModal },
    update: { visible: showUpdateModal, open: openUpdateModal, data: showUpdateModalData, close: closeUpdateModal },
    config: { visible: showConfigModal, open: openConfigModal, data: showConfigModalData, close: closeConfigModal },
    delete: { visible: showDeleteModal, open: openDeleteModal, data: showDeleteModalData, close: closeDeleteModal },
} = modals;
</script>

<template>
    <Head :title="$t('head.corsight_device')" />
    <AdminLayout title="head.corsight_device">
        <AdminPageLayout>
            <CorsightDeviceTableActions :query="params" :rooms="props.rooms" @open="openAddModal" />

            <CorsightDeviceTable :devices="devices" :filters="params" @update="openUpdateModal" @configure="openConfigModal" @delete="openDeleteModal" />

            <Paginate :paginate="devices" />
        </AdminPageLayout>
    </AdminLayout>

    <CorsightDeviceAddModal :show="showAddModal" :rooms="props.rooms" @close="closeAddModal" />
    <CorsightDeviceUpdateModal :show="showUpdateModal" :device="showUpdateModalData!" :rooms="props.rooms" @close="closeUpdateModal" />
    <CorsightDeviceConfigurationModal :show="showConfigModal" :device="showConfigModalData!" @close="closeConfigModal" />
    <CorsightDeviceDeleteModal :show="showDeleteModal" :device="showDeleteModalData!" @close="closeDeleteModal" />
</template>
