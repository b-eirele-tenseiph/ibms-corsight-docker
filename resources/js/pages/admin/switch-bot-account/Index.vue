<script setup lang="ts">
import { AddModal, DeleteModal, ImportModal, ShowCredentialsModal, Table, TableActions, UpdateModal } from '@/components/admin/switch-bot-account';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { SwitchBotAccountProps, type SwitchBotAccountData } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<SwitchBotAccountProps>();

const params = ref({ ...props.filters });

const modals = {
    add: useModal(),
    update: useModal<SwitchBotAccountData>(),
    delete: useModal<Pick<SwitchBotAccountData, 'email' | 'id' | 'room'>>(),
    credentials: useModal<Pick<SwitchBotAccountData, 'id'>>(),
    import: useModal(),
};

const {
    add: { visible: showAddModal, open: openAddModal, close: closeAddModal },
    update: { visible: showUpdateModal, open: openUpdateModal, data: showUpdateModalData, close: closeUpdateModal },
    delete: { visible: showDeleteModal, open: openDeleteModal, data: showDeleteModalData, close: closeDeleteModal },
    credentials: { visible: showCredentialsModal, open: openCredentialsModal, data: showCredentialsData, close: closeCredentialsModal },
    import: { visible: showImportModal, open: openImportModal, close: closeImportModal },
} = modals;
</script>

<template>
    <Head :title="$t('head.switch_bot_account')" />
    <AdminLayout title="head.switch_bot_account">
        <AdminPageLayout>
            <TableActions :query="params" @open="openAddModal" :rooms="props.rooms" @importModal="openImportModal" />

            <Table :account="accounts" :filters="params" @credentials="openCredentialsModal" @update="openUpdateModal" @delete="openDeleteModal" />

            <Paginate :paginate="accounts" />
        </AdminPageLayout>
    </AdminLayout>

    <AddModal :show="showAddModal" :unlinked_room="unlinked_rooms" @close="closeAddModal" />
    <UpdateModal :show="showUpdateModal" :account="showUpdateModalData!" :room="rooms" @close="closeUpdateModal" />
    <DeleteModal :show="showDeleteModal" :account="showDeleteModalData!" @close="closeDeleteModal" />
    <ShowCredentialsModal :show="showCredentialsModal" :account="showCredentialsData!" @close="closeCredentialsModal" />
    <ImportModal :show="showImportModal" @close="closeImportModal" />
</template>
