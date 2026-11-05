<script setup lang="ts">
import {
    CorsightAccountAddModal,
    CorsightAccountDeleteModal,
    CorsightAccountShowCredModal,
    CorsightAccountTable,
    CorsightAccountTableActions,
    CorsightAccountUpdateModal,
} from '@/components/admin/corsight-account';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { CorsightAccountProps, type CorsightAccountData } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<CorsightAccountProps>();

const params = ref({ ...props.filters });

const modals = {
    add: useModal(),
    update: useModal<CorsightAccountData>(),
    delete: useModal<Pick<CorsightAccountData, 'username' | 'id'>>(),
    credentials: useModal<Pick<CorsightAccountData, 'id'>>(),
};

const {
    add: { visible: showAddModal, open: openAddModal, close: closeAddModal },
    update: { visible: showUpdateModal, open: openUpdateModal, data: showUpdateModalData, close: closeUpdateModal },
    delete: { visible: showDeleteModal, open: openDeleteModal, data: showDeleteModalData, close: closeDeleteModal },
    credentials: { visible: showCredentialsModal, open: openCredentialsModal, data: showCredentialsData, close: closeCredentialsModal },
} = modals;
</script>

<template>
    <Head :title="$t('head.corsight_account')" />
    <AdminLayout title="head.corsight_account">
        <AdminPageLayout>
            <CorsightAccountTableActions :query="params" @open="openAddModal" />

            <CorsightAccountTable
                :account="accounts"
                :filters="params"
                @credentials="openCredentialsModal"
                @update="openUpdateModal"
                @delete="openDeleteModal"
            />

            <Paginate :paginate="accounts" />
        </AdminPageLayout>
    </AdminLayout>

    <CorsightAccountAddModal :show="showAddModal" @close="closeAddModal" />
    <CorsightAccountUpdateModal :show="showUpdateModal" :account="showUpdateModalData!" @close="closeUpdateModal" />
    <CorsightAccountDeleteModal :show="showDeleteModal" :account="showDeleteModalData!" @close="closeDeleteModal" />
    <CorsightAccountShowCredModal :show="showCredentialsModal" :account="showCredentialsData!" @close="closeCredentialsModal" />
</template>
