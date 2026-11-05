<script setup lang="ts">
import {
    GatewayActions,
    GatewayAddModal,
    GatewayDeleteModal,
    GatewayFilters,
    GatewayTable,
    GatewayUpdateModal,
} from '@/components/admin/lorawan-gateway';
import { Paginate } from '@/components/shared/pagination';
import { useModal } from '@/composables/useModal';
import { AdminPageLayout } from '@/layouts/admin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { LorawanGateway, LorawanGatewayProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<LorawanGatewayProps>();

const ADD_MODAL = useModal();
const UPDATE_MODAL = useModal<LorawanGateway>();
const DELETE_MODAL = useModal<LorawanGateway>();
</script>

<template>
    <Head :title="$t('head.lorawan_gateway')" />
    <AdminLayout title="head.lorawan_gateway">
        <AdminPageLayout>
            <!-- Filters -->
            <div class="flex flex-row justify-between gap-4">
                <GatewayFilters :query-params="queryParams" :floors="floors" />
                <GatewayActions @add="ADD_MODAL.open" />
            </div>
            <!-- Table -->
            <GatewayTable :gateways="gateways.data" :query-params="queryParams" @update="UPDATE_MODAL.open" @delete="DELETE_MODAL.open" />
            <!-- Pagination -->
            <Paginate :paginate="gateways" />
        </AdminPageLayout>
    </AdminLayout>

    <!-- Modals -->
    <GatewayAddModal :show="ADD_MODAL.visible.value" :connections="connection" :floors="floors" @close="ADD_MODAL.close" />

    <GatewayUpdateModal
        v-if="UPDATE_MODAL.data.value"
        :show="UPDATE_MODAL.visible.value"
        :gateway="UPDATE_MODAL.data.value"
        :connections="connection"
        :floors="floors"
        @close="UPDATE_MODAL.close"
    />
    <GatewayDeleteModal
        v-if="DELETE_MODAL.visible.value && DELETE_MODAL.data.value"
        :open="DELETE_MODAL.visible.value"
        :gateway="DELETE_MODAL.data.value!"
        @close="DELETE_MODAL.close"
    />
</template>
