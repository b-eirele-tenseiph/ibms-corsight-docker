<script setup lang="ts">
import { CardForm } from '@/components/shared/card';
import { Button } from '@/components/ui/button';
import { Food, RestaurantFoodOrderHistory, User } from '@/types';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    cart: { food: Food; quantity: number }[];
    onRead: (restaurantFoodOrderHistory: RestaurantFoodOrderHistory) => void;
    restaurantFoodOrderHistory: RestaurantFoodOrderHistory | null;
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'confirm-order'): void;
}>();

const isLoading = ref(false);

const subtotal = computed(() => props.cart.reduce((sum, item) => sum + Number(item.food.price) * item.quantity, 0));

const page = usePage<{ auth: { user: User } }>();
</script>

<template>
    <CardForm>
        <template #header-title>{{ $t('page.restaurant.label.order_detail') }}</template>
        <template #header-button>
            <Icon icon="lucide:history" class="h-5 w-5 cursor-pointer md:h-6 md:w-6" @click="onRead(restaurantFoodOrderHistory)" />
        </template>

        <template #content-detail-additionals>
            <div class="flex flex-col gap-y-2.5">
                <div class="bg-background button flex justify-between rounded-[6px] border p-2">
                    <p class="fluid-text-xxs-sm font-medium capitalize">{{ $t('page.restaurant.label.room_name') }}</p>
                    <p class="fluid-text-xxs-sm font-medium capitalize">{{ page.props.auth.user.room_name }}</p>
                </div>
                <div class="bg-background flex justify-between rounded-[6px] border p-2">
                    <p class="fluid-text-xxs-sm font-medium capitalize">{{ $t('page.restaurant.label.customer_name') }}</p>
                    <p class="fluid-text-xxs-sm font-semibold capitalize">{{ page.props.auth.user.name }}</p>
                </div>
            </div>
        </template>

        <template #content-details>
            <div class="flex flex-1 flex-col gap-y-4.5">
                <div v-for="item in cart" :key="item.food.id" class="flex justify-between gap-x-4 p-2">
                    <div class="flex flex-col gap-y-1">
                        <p class="fluid-text-xxs-sm font-semibold capitalize">{{ item.food.name }}</p>
                        <p class="text-secondary-text fluid-text-xxs-xs font-medium capitalize">{{ item.quantity }} × ₱ {{ item.food.price }}</p>
                    </div>
                    <div class="text-primary-text fluid-text-xxs-sm text-sm font-semibold">
                        ₱
                        {{
                            (Number(item.food.price) * item.quantity).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })
                        }}
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-between">
                <p class="text-secondary-text fluid-text-xxs-sm font-semibold">{{ $t('page.restaurant.label.total_payment') }}</p>
                <p class="text-accent-foreground fluid-text-xs-base font-semibold">
                    ₱
                    {{ subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </p>
            </div>
            <div class="flex justify-end gap-x-3">
                <Button variant="outline" @click="emit('back')" class="fluid-text-xxs-sm px-3 py-2 font-semibold">{{ $t('action.go_back') }}</Button>
                <Button
                    variant="default"
                    @click="(emit('confirm-order'), (isLoading = true))"
                    class="fluid-text-xxs-sm px-3 py-2 font-semibold"
                    :disabled="isLoading"
                    >{{ $t('action.place_order') }}</Button
                >
            </div>
        </template>
    </CardForm>
</template>
