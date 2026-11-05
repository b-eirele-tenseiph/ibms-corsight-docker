<script setup lang="ts">
import OrderCard from '@/components/crew/order/OrderCard.vue';
import StateEmpty from '@/components/shared/states/StateEmpty.vue';
import { useBreakpoints } from '@/composables/useBreakpoints';
import AdminPageLayout from '@/layouts/admin/AdminPageLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { RestaurantFoodOrderHistory } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { debouncedRef } from '@vueuse/core';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import OrderHeader from '../partials/OrderHeader.vue';

const { isMobile, isTablet, isLaptop, isDesktop } = useBreakpoints();

const getScreenSize = computed(() => {
    if (isDesktop.value) return 'grid-cols-5';
    if (isLaptop.value) return 'grid-cols-4';
    if (isTablet.value) return 'grid-cols-3';
    if (isMobile.value) return 'grid-cols-2';
    return 'grid-cols-1';
});

const props = defineProps<{
    orders: RestaurantFoodOrderHistory[];
}>();

const orders = ref(props.orders);
type OrderPlacedEvent = {
    order: RestaurantFoodOrderHistory;
};

onMounted(() => {
    useEcho(`food-orders`, '.order.placed.new', (data: OrderPlacedEvent) => {
        const orderToUpdate = orders.value.find((o) => o.id === data.order.id);

        if (orderToUpdate) {
            orderToUpdate.status = data.order.status;
        } else {
            orders.value.push(data.order);
        }
    }).listen();
});
// --- Filters ---
type OrderFilter = 'Processing' | 'New' | 'On cook' | 'Completed' | 'Cancelled';
const activeFilter = ref<OrderFilter>('Processing');
const searchInput = ref<string>('');
const searchTerm = debouncedRef(searchInput, 200);

const applyFilter = (value: OrderFilter) => {
    activeFilter.value = value;
};

// (optional) normalize slight label/status mismatches
const normalize = (s: string) => s.toLowerCase().replace(/\s+/g, ' ').trim();
const statusMap: Record<string, string> = {
    new: 'new',
    // 'new orders': 'new order',
    'on cook': 'on cook',
    completed: 'completed',
    cancelled: 'cancelled',
};

const filteredOrders = computed(() => {
    const term = normalize(searchTerm.value || '');

    return orders.value.filter((order) => {
        const currentStatus = normalize(order.status || '');

        // 1. Status Filter Logic
        if (activeFilter.value === 'Processing') {
            if (currentStatus === 'completed' || currentStatus === 'cancelled') {
                return false;
            }
        } else {
            const target = statusMap[normalize(activeFilter.value)] || normalize(activeFilter.value);
            if (currentStatus !== target) return false;
        }

        // 2. Search Logic
        if (!term) return true;

        // Extract food names from the details relationship
        const foodItems = order.details?.map((detail) => detail.food?.name) || [];

        const haystack = [
            order.id?.toString(),
            order.user?.name,
            order.room?.name,
            order.status,
            ...foodItems, // Add the array of food names into the haystack
        ]
            .filter(Boolean)
            .map((v) => normalize(String(v)))
            .join(' | ');

        return haystack.includes(term);
    });
});

const { stopListening: stopOrder, listen: listenOrder } = useEcho(`food-orders`, '.order.placed.new', (data: any) => {
    const orderToUpdate = orders.value.find((o) => o.id === data.order.id);
    if (orderToUpdate) {
        orderToUpdate.status = data.order.status;
    } else {
        orders.value.push(data.order);
    }
});

onMounted(() => {
    listenOrder();
});

onUnmounted(() => {
    stopOrder();
});
</script>

<template>
    <Head :title="$t('head.dashboard')" />
    <AdminLayout title="head.dashboard">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-4">
                <OrderHeader v-model="searchInput" :active-filter="activeFilter" @filter-change="applyFilter" />
            </div>

            <div class="grid w-full gap-1 space-x-1" :class="[getScreenSize]">
                <OrderCard v-for="order in filteredOrders" :key="order.id" :order="order" />
            </div>

            <div>
                <StateEmpty v-if="!filteredOrders.length" />
            </div>
        </AdminPageLayout>
    </AdminLayout>
</template>
