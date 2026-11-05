<script setup lang="ts">
import { RoomActions, RoomAddModal, RoomDeleteModal, RoomFilters, RoomImportModal, RoomTable, RoomUpdateModal } from '@/components/admin/room';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Room } from '@/types';
import { RoomProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<RoomProps>();

// Add Modal
const ADD_MODAL = useModal();
// Update Modal
const UPDATE_MODAL = useModal<Room>();
// Delete Modal
const DELETE_MODAL = useModal<{ id: number; name: string }>();
// Import Modal
const IMPORT_MODAL = useModal();
</script>

<template>
    <Head :title="$t('head.room_management')" />
    <AdminLayout title="head.room_management">
        <AdminPageLayout>
            <!-- Filters -->
            <div class="flex flex-row justify-between gap-4">
                <RoomFilters :floors="floors" :query-params="queryParams" />
                <RoomActions @add="ADD_MODAL.open" @import="IMPORT_MODAL.open" />
            </div>
            <!-- Table -->
            <RoomTable :rooms="rooms.data" @update="UPDATE_MODAL.open" @delete="DELETE_MODAL.open" :query-params="queryParams" />
            <!-- Pagination -->
            <Paginate :paginate="rooms" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modals -->
    <RoomAddModal :show="ADD_MODAL.visible.value" :external_apps="external_apps" :floors="floors" @close="ADD_MODAL.close" />

    <RoomUpdateModal
        v-if="UPDATE_MODAL.visible.value && UPDATE_MODAL.data.value"
        :show="UPDATE_MODAL.visible.value"
        :room="UPDATE_MODAL.data.value"
        :floors="floors"
        :external_apps="external_apps"
        @close="UPDATE_MODAL.close"
    />

    <RoomDeleteModal
        v-if="DELETE_MODAL.visible.value && DELETE_MODAL.data.value"
        :open="DELETE_MODAL.visible.value"
        :room="DELETE_MODAL.data.value!"
        @close="DELETE_MODAL.close"
    />

    <RoomImportModal :show="IMPORT_MODAL.visible.value" @close="IMPORT_MODAL.close" />
</template>
