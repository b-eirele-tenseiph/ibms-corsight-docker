<script setup lang="ts">
import { AdminActions, AdminAddModal, AdminDeleteModal, AdminFilters, AdminTable, AdminUpdateModal } from '@/components/admin/admin-account';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Admin, AdminProps } from '@/types/t-admin';
import { Head } from '@inertiajs/vue3';

defineProps<AdminProps>();

const ADD_MODAL = useModal();
const UPDATE_MODAL = useModal<Admin>();
const DELETE_MODAL = useModal<Admin>();
</script>

<template>
    <Head :title="$t('head.admin_management')" />
    <AdminLayout title="head.admin_management">
        <AdminPageLayout>
            <div class="flex w-full items-center justify-between">
                <AdminFilters :roles="roles" :query-params="queryParams" />
                <AdminActions class="self-start" @add="ADD_MODAL.open" />
            </div>
            <!-- Admin Table -->
            <AdminTable :admins="admins.data" :query-params="queryParams" @update="UPDATE_MODAL.open" @delete="DELETE_MODAL.open" />
            <!-- Pagination -->
            <Paginate :paginate="admins" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modals -->
    <AdminAddModal :show="ADD_MODAL.visible.value" :roles @close="ADD_MODAL.close" />

    <AdminUpdateModal
        v-if="UPDATE_MODAL.data.value"
        :show="UPDATE_MODAL.visible.value"
        :admin="UPDATE_MODAL.data.value"
        :roles
        @close="UPDATE_MODAL.close"
    />

    <AdminDeleteModal
        v-if="DELETE_MODAL.data.value"
        :open="DELETE_MODAL.visible.value"
        :admin="DELETE_MODAL.data.value!"
        @close="DELETE_MODAL.close"
    />
</template>
