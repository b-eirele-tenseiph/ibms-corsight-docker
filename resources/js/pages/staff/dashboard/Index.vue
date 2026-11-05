<script setup lang="ts">
import { Paginate } from '@/components/shared/pagination';
import { StateEmpty } from '@/components/shared/states';
import { AddAccessModal, DeleteAccessModal, RoomCard, ShowAccessModal, StatusFilter, UpdateAccessModal } from '@/components/staff/dashboard';
import { Label } from '@/components/ui/label';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { Room, RoomProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<RoomProps>();

const params = reactive({ ...props.queryParams });

// Modal
const SHOW_ACCESS_MODAL = useModal<{ room: Room }>();
const UPDATE_MODAL = useModal<any>();
const DELETE_MODAL = useModal<any>();
const ADD_MODAL = useModal<Room>();

// Methods
function handleRoomClick(room: Room) {
    SHOW_ACCESS_MODAL.open({ room });
}
function closeAllModals() {
    ADD_MODAL.close();
    UPDATE_MODAL.close();
    SHOW_ACCESS_MODAL.close();
    DELETE_MODAL.close();
}
</script>

<template>
    <Head :title="$t('head.front_desk')" />
    <AdminLayout title="head.front_desk">
        <AdminPageLayout>
            <Label class="fluid-text-base-xl font-bold">{{ $t('head.room_overview') }}</Label>
            <!-- Filters -->
            <div class="flex flex-row justify-between gap-2">
                <StatusFilter :data="params" />
            </div>
            <div v-if="props.rooms.data.length" class="h-full grow overflow-y-auto">
                <RoomCard :rooms="props.rooms.data" @clickRoom="handleRoomClick" />
            </div>
            <div v-else class="flex h-full w-full items-start justify-center">
                <StateEmpty class="py-4" />
            </div>
            <!-- Pagination -->
            <Paginate :paginate="props.rooms" :params="params" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modals -->
    <ShowAccessModal
        v-if="SHOW_ACCESS_MODAL.visible.value && SHOW_ACCESS_MODAL.data.value"
        :showAccessModal="{
            show: SHOW_ACCESS_MODAL.visible.value,
            room: SHOW_ACCESS_MODAL.data.value.room,
            onCancel: SHOW_ACCESS_MODAL.close,
            onSubmit: SHOW_ACCESS_MODAL.close,
        }"
        @edit="UPDATE_MODAL.open"
        @delete="DELETE_MODAL.open"
        @add="ADD_MODAL.open"
    />
    <AddAccessModal
        v-if="ADD_MODAL.visible.value && ADD_MODAL.data.value"
        :open="ADD_MODAL.visible.value"
        :room="ADD_MODAL.data.value"
        @close="ADD_MODAL.close"
        @close-all="closeAllModals"
    />
    <UpdateAccessModal
        v-if="UPDATE_MODAL.visible.value && UPDATE_MODAL.data.value"
        :open="UPDATE_MODAL.visible.value"
        :user="UPDATE_MODAL.data.value"
        :rooms="props.rooms.data"
        @close="UPDATE_MODAL.close"
        @close-all="closeAllModals"
    />
    <DeleteAccessModal
        v-if="DELETE_MODAL.visible.value && DELETE_MODAL.data.value"
        :open="DELETE_MODAL.visible.value"
        :access="DELETE_MODAL.data.value"
        @close="DELETE_MODAL.close"
        @close-all="closeAllModals"
    />
</template>
