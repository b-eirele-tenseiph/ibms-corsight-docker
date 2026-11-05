<script setup lang="ts">
import { OrderHistory } from '@/components/crew/order';
import OrderFilters from '@/components/crew/order/OrderFilters.vue';
import Paginate from '@/components/shared/pagination/Paginate.vue';
import AdminPageLayout from '@/layouts/admin/AdminPageLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Filters } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    orders: any;
    filters: Filters;
}>();
</script>

<template>
    <Head :title="$t('head.order_history')" />
    <AdminLayout title="head.order_history">
        <AdminPageLayout>
            <div class="fluid-text-xs-sm flex w-full flex-row justify-between">
                <!-- bind v-model so it emits update:modelValue to your InputSearch -->
                <OrderFilters :query-params="filters" />
                <!-- insert floor filter -->
                <!-- insert table -->
            </div>
            <OrderHistory :orders="orders.data" :query-params="filters"></OrderHistory>
            <Paginate :paginate="orders" />
        </AdminPageLayout>
    </AdminLayout>
</template>
