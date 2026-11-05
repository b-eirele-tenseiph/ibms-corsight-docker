<script setup lang="ts">
import { CorsightPersonDetailModal, CorsightPersonTable, CorsightPersonTableActions } from '@/components/admin/corsight-person';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminLayout, AdminPageLayout } from '@/layouts/admin';
import { CorsightPersonProps, type CorsightPersonSessionData } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<CorsightPersonProps>();

const params = ref({ ...props.filters });

const modals = {
    view: useModal<CorsightPersonSessionData>(),
};

const {
    view: { visible: showViewModal, open: openViewModal, data: showViewModalData, close: closeViewModal },
} = modals;
</script>

<template>
    <Head :title="$t('head.corsight_person')" />
    <AdminLayout title="head.corsight_person">
        <AdminPageLayout>
            <CorsightPersonTableActions :query="params" :devices="devices" />

            <CorsightPersonTable :people="people" :filters="params" @view="openViewModal" />

            <Paginate :paginate="people" />
        </AdminPageLayout>
    </AdminLayout>

    <CorsightPersonDetailModal :show="showViewModal" :person="showViewModalData!" @close="closeViewModal" />
</template>
