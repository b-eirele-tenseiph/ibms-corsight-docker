<script setup lang="ts">
import { SheetForm } from '@/components/shared/sheets';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { RestaurantFoodOrderHistory } from '@/types';
import { useEcho } from '@laravel/echo-vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { RestaurantFoodOrderHistoryItem } from '.';

const props = defineProps<{
    open: boolean;
    orders: RestaurantFoodOrderHistory[];
}>();

defineEmits(['close']);

const localOrders = ref<RestaurantFoodOrderHistory[]>([]);

watch(
    () => props.orders,
    (newOrders) => {
        localOrders.value = [...newOrders];
    },
    { immediate: true },
);

const { stopListening: stopOrderStatus, listen: listenOrderStatus } = useEcho(`order-status-updated`, '.order.status.new', (data: any) => {
    const orderToUpdate = localOrders.value.find((o) => o.id === data.order.id);

    if (orderToUpdate) {
        orderToUpdate.status = data.order.status;
        orderToUpdate.remarks = data.order.remarks;
    }
});

onMounted(() => {
    listenOrderStatus();
});

onUnmounted(() => {
    stopOrderStatus();
});
</script>

<template>
    <SheetForm :open="open" @close="$emit('close')" :customClosable="true" class="pb-10" tabindex="-1">
        <div class="flex flex-col gap-x-4">
            <div class="flex flex-col gap-1">
                <h1 class="mt-3 font-bold">{{ $t('form.label.order_history') }}</h1>
                <div class="flex flex-1 items-center gap-3">
                    <hr class="border-secondary-light-gray flex-1 border-t" />
                </div>
            </div>

            <div class="flex max-h-[86svh] flex-col gap-x-4">
                <ScrollArea class="my-5 flex-1 overflow-auto pr-2.5">
                    <div class="flex flex-col gap-y-5">
                        <RestaurantFoodOrderHistoryItem v-for="order in localOrders" :key="order.id" :order="order" />
                    </div>
                    <ScrollBar orientation="vertical" />
                </ScrollArea>
            </div>
        </div>
    </SheetForm>
</template>
