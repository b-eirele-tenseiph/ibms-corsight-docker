<script setup lang="ts">
import { Paginate } from '@/components/shared/pagination';
import {
    PromoActions,
    PromoAddModal,
    PromoDeleteModal,
    PromoFilter,
    PromoImageViewerModal,
    PromoTable,
    PromoUpdateModal,
} from '@/components/staff/promo';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { Promo, PromoProps } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<PromoProps>();

const ADD_MODAL = useModal<Promo>();
const UPDATE_MODAL = useModal<Promo>();
const DELETE_MODAL = useModal<Promo>();
const IMAGE_MODAL = useModal<Promo>();
</script>

<template>
    <Head :title="$t('head.promo')" />
    <AdminLayout title="head.promo">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-x-4">
                <PromoFilter :queryParams="props.queryParams" />

                <PromoActions @onAdd="ADD_MODAL.open" />
            </div>

            <PromoTable
                :promos="props.promos"
                :onEdit="UPDATE_MODAL.open"
                :onDelete="DELETE_MODAL.open"
                :queryParams="props.queryParams"
                @onView="IMAGE_MODAL.open"
            />

            <Paginate :paginate="props.promos" />
        </AdminPageLayout>
    </AdminLayout>

    <PromoAddModal :open="ADD_MODAL.visible.value" @close="ADD_MODAL.close" />

    <PromoUpdateModal :open="UPDATE_MODAL.visible.value" @close="UPDATE_MODAL.close" :promo="UPDATE_MODAL.data.value" />

    <PromoDeleteModal :open="DELETE_MODAL.visible.value" @close="DELETE_MODAL.close" :promo="DELETE_MODAL.data.value" />

    <PromoImageViewerModal :open="IMAGE_MODAL.visible.value" :promo="IMAGE_MODAL.data.value" @close="IMAGE_MODAL.close" />
</template>
