<script lang="ts" setup>
import { AddModal, DeleteModal, Search } from '@/components/admin/roles';
import RoleActions from '@/components/admin/roles/RoleActions.vue';
import RoleTable from '@/components/admin/roles/RoleTable.vue';
import Paginate from '@/components/shared/pagination/Paginate.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { SettingsLayout } from '@/layouts/settings';
import type { PaginateWithExtras, Permission, Role } from '@/types/t-role';
import { Head } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

// Props received from Inertia
const props = defineProps<{
    roles: PaginateWithExtras<Role>;
    permissions: Permission[];
    filters: Record<string, any>;
}>();

// Add modal
const addModal = reactive({
    show: false,
    open: () => (addModal.show = true),
    close: () => (addModal.show = false),
});

// Delete modal
const deleteModal = reactive({
    show: false,
    selectedRole: null as Role | null,
    open: (role: Role) => {
        deleteModal.selectedRole = role;
        deleteModal.show = true;
    },
    close: () => {
        deleteModal.show = false;
        deleteModal.selectedRole = null;
    },
});

// Search and Sorting Parameters
const params = reactive({ ...props.filters });

// Roles data to pass to RoleTable
const filteredRoles = computed(() => props.roles.data || []);
</script>

<template>
    <Head :title="$t('head.general_settings')" />
    <AdminLayout title="head.general_settings">
        <SettingsLayout>
            <div class="flex flex-row justify-between gap-4">
                <Search :data="params" />
                <RoleActions @add="addModal.open" />
            </div>

            <RoleTable :params="params" :roles="filteredRoles" :allPermissions="permissions" @delete-role="deleteModal.open" />

            <Paginate :paginate="roles" />
        </SettingsLayout>
    </AdminLayout>
    <AddModal :isOpen="addModal.show" :roles="props.roles.data" :permissions="props.permissions" :isProcessing="false" @close="addModal.close" />
    <DeleteModal
        :isOpen="deleteModal.show"
        :role="deleteModal.selectedRole"
        :roles="props.roles.data"
        :permissions="props.permissions"
        :isProcessing="false"
        @close="deleteModal.close"
    />
</template>
