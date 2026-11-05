<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardAction, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatReadableDate } from '@/lib/utils';
import { ordersCancel } from '@/routes/crew/dashboard';
import { statusUpdate } from '@/routes/crew/orders';
import { Icon } from '@iconify/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { OrderCancelModal } from '.';

const props = defineProps<{
    order: Record<string, any>;
}>();

const localOrder = reactive({ ...props.order });

// A computed property to calculate the total amount
const orderTotal = computed(() => {
    return localOrder.details.reduce((sum: number, detail: any) => {
        return sum + detail.quantity * detail.unit_price;
    }, 0); // The '0' is the initial value for the sum
});

const totalQuantity = computed(() => {
    return localOrder.details.reduce((sum: number, detail: any) => {
        return sum + detail.quantity;
    }, 0); // The '0' is the initial value for the sum
});

const orderStatus = ref<string>(localOrder.status);

const norm = (s: string) => s?.toLowerCase().trim();

const form = useForm<{
    action: string | null;
}>({
    action: 'accept',
});

const getBadgeClass = computed(() => {
    const s = orderStatus.value;
    if (s === 'new') return 'bg-info-bg text-info-text';
    if (s === 'on cook') return 'bg-warning-bg text-warning-text';
    if (s === 'completed') return 'bg-success-bg text-success-text';
    if (s === 'cancelled') return 'bg-error-bg text-error-text';
    return 'bg-info-bg text-info-text';
});

const getButtonText = computed(() => {
    const s = orderStatus.value;
    if (s === 'new') return 'accept';
    if (s === 'on cook') return 'complete';
    if (s === 'completed') return '';
    if (s === 'cancelled') return '';
    return form.action;
});

const getButtonTextColor = computed(() => {
    const s = orderStatus.value;
    if (s === 'new') return 'text-info-text';
    if (s === 'on cook') return 'text-warning-text';
    if (s === 'completed') return 'text-success-text';
    if (s === 'cancelled') return 'text-error-text';
    return 'text-info-text';
});

const isLoading = ref<boolean>(false);

function onActionClick() {
    isLoading.value = true;
    form.action = getButtonText.value;

    form.put(statusUpdate({ order: localOrder.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
        },
    });
}

const borderByStatus: Record<string, string> = {
    new: 'border-b-info-text',
    'on cook': 'border-b-warning-text',
    completed: 'border-b-success-text',
    cancelled: 'border-b-error-text',
};

const statusBorderClass = computed(() => borderByStatus[norm(orderStatus.value)] ?? 'border-b-info-text');

watch(
    () => localOrder.status,
    (value) => {
        orderStatus.value = value;
    },
);

const showCancelModal = ref(false);
const isCancelling = ref(false);

function handleCancelConfirm(reason: string) {
    isCancelling.value = true;
    router.put(
        ordersCancel({ order: localOrder.id }),
        {
            remarks: reason,
        },
        {
            onSuccess: () => {
                showCancelModal.value = false;
                localOrder.remarks = reason;
                orderStatus.value = 'cancelled';
            },
            onFinish: () => {
                isCancelling.value = false;
            },
        },
    );
}
</script>

<template>
    <Card
        :class="[getButtonTextColor.replace('text-badge', 'border-b-badge'), 'w-full gap-0 border-b-2', statusBorderClass]"
        class="fluid-text-xxs-sm flex h-auto min-h-67 flex-col px-0 py-3"
    >
        <CardHeader class="px-3 py-1">
            <div class="flex flex-col">
                <div class="flex flex-row justify-between uppercase">
                    <CardDescription>{{ $t('page.crew_dashboard.label.order_number') }}{{ order.id }}</CardDescription>
                    <CardAction>
                        <Badge :class="getBadgeClass" class="capitalize">
                            {{ localOrder.status == 'Completed' ? 'On Cook' : localOrder.status }}
                        </Badge>
                    </CardAction>
                </div>
                <div>
                    <CardTitle class="fluid-text-xs-base">{{ localOrder.user.name }}</CardTitle>
                </div>
            </div>
        </CardHeader>

        <div class="mx-5 flex flex-col border-b border-gray-200 py-1">
            <div class="flex items-center">
                <Icon icon="prime:calendar" width="18" height="18" style="color: primary-text" />
                <a class="ml-1">{{ formatReadableDate(order.created_at) }}</a>
            </div>
            <div class="flex items-center">
                <Icon icon="tabler:building" width="18" height="18" style="color: primary-text" />
                <a class="ml-1">{{ order.room.name }}</a>
            </div>
        </div>

        <div class="mx-5 flex min-h-30 flex-col border-b border-gray-200 py-1 font-light">
            <div class="flex w-full flex-row pb-1 font-bold">
                <div class="grow">
                    {{ totalQuantity }} {{ totalQuantity > 1 ? $t('page.crew_dashboard.label.items') : $t('page.crew_dashboard.label.item') }}
                </div>
                <div :class="[getButtonTextColor]">
                    {{ orderTotal.toFixed(2) }}
                </div>
            </div>
            <div class="max-h-22 overflow-auto pr-1">
                <div v-for="detail in order.details" :key="detail.id" class="flex max-h-20 w-full flex-row">
                    <div>{{ detail.quantity }}</div>
                    <div class="grow pl-1">{{ detail.food.name }}</div>
                    <div>{{ detail.total_amount }}</div>
                </div>
            </div>
        </div>

        <div
            v-if="orderStatus === 'cancelled' && order.remarks"
            class="bg-error-bg/10 border-error-text/20 mx-5 my-2 flex flex-col rounded-md border p-2"
        >
            <span class="text-error-text text-[10px] font-bold tracking-tight uppercase"
                >{{ $t('page.crew_dashboard.label.reason_on_cancelling') }}:</span
            >
            <p class="text-error-text text-[11px] leading-snug italic">"{{ order.remarks }}"</p>
        </div>

        <div
            v-if="orderStatus !== 'completed' && orderStatus !== 'cancelled'"
            :class="[getButtonTextColor, 'mx-5 flex items-center justify-end gap-2 border-t border-gray-200 pt-1']"
        >
            <button
                v-if="orderStatus === 'new'"
                type="button"
                class="fluid-text-xs-sm text-error-text cursor-pointer capitalize underline"
                @click="showCancelModal = true"
            >
                {{ $t('action.cancel') }}
            </button>

            <button
                :disabled="isLoading"
                :class="[isLoading ? 'text-gray-200' : '']"
                class="fluid-text-xs-sm cursor-pointer capitalize underline"
                @click="onActionClick"
            >
                {{ getButtonText }}
            </button>
        </div>

        <OrderCancelModal v-model:open="showCancelModal" :order-id="order.id" :loading="isCancelling" @confirm="handleCancelConfirm" />
    </Card>
</template>
