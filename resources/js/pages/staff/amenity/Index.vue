<script setup lang="ts">
import { Paginate } from '@/components/shared/pagination';
import {
    AmenityActions,
    AmenityAddModal,
    AmenityDeleteModal,
    AmenityDetailsSheet,
    AmenityFilters,
    AmenityTable,
    AmenityUpdateModal,
} from '@/components/staff/amenity';
import { useModal, useModal as useSheet } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { Amenity, PaginatedResponse } from '@/types';
import { AmenityCategory } from '@/types/t-amenity-category';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    amenities: PaginatedResponse<Amenity>;
    amenityCategories: AmenityCategory[];
    queryParams: object;
}>();

const ADD_MODAL = useModal<Amenity>();
const UPDATE_MODAL = useModal<Amenity>();
const DELETE_MODAL = useModal<Amenity>();
const READ_SHEET = useSheet<Amenity>();
</script>

<template>
    <Head :title="$t('head.amenity')" />
    <AdminLayout title="head.amenity">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-x-4">
                <AmenityFilters :queryParams="queryParams" :amenityCategories="amenityCategories" />

                <AmenityActions @onAdd="ADD_MODAL.open" />
            </div>

            <AmenityTable
                :amenities="props.amenities"
                :onEdit="UPDATE_MODAL.open"
                :onDelete="DELETE_MODAL.open"
                :onRead="READ_SHEET.open"
                :queryParams="props.queryParams"
            />

            <Paginate :paginate="amenities" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Add Amenity Modal Component -->
    <AmenityAddModal :open="ADD_MODAL.visible.value" @close="ADD_MODAL.close" :amenityCategories="props.amenityCategories" />

    <!-- Update Amenity Modal Component -->
    <AmenityUpdateModal
        :open="UPDATE_MODAL.visible.value"
        :amenity="UPDATE_MODAL.data.value"
        :amenityCategories="props.amenityCategories"
        @close="UPDATE_MODAL.close"
    />

    <!-- Delete Amenity Modal Component -->
    <AmenityDeleteModal :open="DELETE_MODAL.visible.value" :amenity="DELETE_MODAL.data.value" @close="DELETE_MODAL.close" />

    <!-- Read Amenity Sheet Component -->
    <AmenityDetailsSheet :open="READ_SHEET.visible.value" @close="READ_SHEET.close" :amenity="READ_SHEET.data.value" />
</template>
