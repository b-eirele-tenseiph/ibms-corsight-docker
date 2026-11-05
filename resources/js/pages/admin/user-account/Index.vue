<script setup lang="ts">
import { UserActions, UserAddModal, UserDeleteModal, UserFilters, UserTable, UserUpdateModal } from '@/components/admin/user-account';
import { Paginate } from '@/components/shared/pagination';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { AccountManagementUser, PaginatedResponse, Room } from '@/types';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps<{
    users: PaginatedResponse<AccountManagementUser>;
    rooms: Room[];
    filters: Record<string, any>;
}>();

const params = ref({ ...props.filters });

const ADD_MODAL = reactive({
    show: false,
    open: () => (ADD_MODAL.show = true),
    close: () => (ADD_MODAL.show = false),
});

const EDIT_MODAL = reactive({
    show: false,
    user: null as AccountManagementUser | null,
    open: (user: AccountManagementUser) => {
        EDIT_MODAL.show = true;
        EDIT_MODAL.user = user;
    },
    close: () => {
        EDIT_MODAL.show = false;
        EDIT_MODAL.user = null;
    },
});

const DELETE_MODAL = reactive({
    show: false,
    user: null as AccountManagementUser | null,
    open: (user: AccountManagementUser) => {
        DELETE_MODAL.show = true;
        DELETE_MODAL.user = user;
    },
    close: () => {
        DELETE_MODAL.show = false;
        DELETE_MODAL.user = null;
    },
});
</script>

<template>
    <Head :title="$t('head.user_management')" />
    <AdminLayout title="head.user_management">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-x-4">
                <UserFilters :queryParams="params" :rooms="rooms" />

                <UserActions :onAdd="ADD_MODAL.open" />
            </div>

            <UserTable :users="props.users" :onEdit="EDIT_MODAL.open" :onDelete="DELETE_MODAL.open" :queryParams="params" />

            <Paginate :paginate="users" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Add User Modal -->
    <UserAddModal :open="ADD_MODAL.show" @close="ADD_MODAL.close" :rooms="rooms" />

    <!-- Edit User Modal -->
    <UserUpdateModal :user="EDIT_MODAL.user" :open="EDIT_MODAL.show" @close="EDIT_MODAL.close" :rooms="rooms" />

    <!-- Delete User Modal -->
    <UserDeleteModal :user="DELETE_MODAL.user" :open="DELETE_MODAL.show" @close="DELETE_MODAL.close" />
</template>
