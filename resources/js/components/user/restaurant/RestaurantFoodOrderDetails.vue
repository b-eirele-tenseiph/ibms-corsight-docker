<script setup lang="ts">
import { CardForm } from '@/components/shared/card';
import { Button } from '@/components/ui/button';
import { Food, RestaurantFoodOrderHistory } from '@/types';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{
    cart: { food: Food; quantity: number }[];
    onRead: (restaurantFoodOrderHistory: RestaurantFoodOrderHistory) => void;
    restaurantFoodOrderHistory: RestaurantFoodOrderHistory | null;
}>();

const emit = defineEmits<{
    (e: 'clear-cart'): void;
    (e: 'checkout'): void;
}>();

const subtotal = computed(() => {
    return props.cart.reduce((sum, item) => sum + Number(item.food.price) * item.quantity, 0);
});
</script>

<template>
    <CardForm>
        <template #header-title>{{ $t('page.restaurant.label.order_detail') }}</template>
        <template #header-button>
            <Icon icon="lucide:history" class="h-5 w-5 cursor-pointer md:h-6 md:w-6" @click="onRead(restaurantFoodOrderHistory)" />
        </template>

        <template #content-button>
            <div class="flex justify-end" v-if="cart.length > 0">
                <Button variant="link" class="fluid-text-xxs-xs text-destructive p-0 font-semibold" @click="emit('clear-cart')">
                    {{ $t('action.clear_items') }}
                </Button>
            </div>
        </template>

        <template #content-details>
            <div class="flex flex-1 flex-col gap-y-4.5">
                <div v-for="item in cart" :key="item.food.id" class="flex justify-between rounded-md px-3">
                    <div class="flex flex-col gap-y-1">
                        <p class="fluid-text-xxs-sm text-primary-text font-semibold capitalize">
                            {{ item.food.name }}
                        </p>
                        <p class="fluid-text-xxs-xs text-secondary-text font-medium">
                            {{ item.quantity }} item<span v-if="item.quantity > 1">s</span>
                        </p>
                    </div>
                    <div class="flex items-center">
                        <p class="fluid-text-xxs-sm text-primary-text font-semibold">
                            ₱
                            {{
                                (Number(item.food.price) * item.quantity).toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                })
                            }}
                        </p>
                    </div>
                </div>

                <div v-if="cart.length === 0" class="flex flex-col gap-y-3">
                    <div class="flex justify-center">
                        <Icon icon="lucide:shopping-cart" class="text-secondary-text h-10 w-10 opacity-30" />
                    </div>
                    <p class="text-secondary-muted-text fluid-text-xs-sm text-center font-semibold">{{ $t('page.restaurant.state.empty_cart') }}</p>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-between">
                <p class="text-secondary-text fluid-text-xxs-sm font-semibold">{{ $t('page.restaurant.label.subtotal') }}</p>
                <p class="text-accent-foreground fluid-text-xs-base font-semibold">
                    ₱
                    {{ subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </p>
            </div>
            <div class="flex flex-wrap justify-end gap-x-3">
                <Button variant="default" :disabled="cart.length === 0" class="fluid-text-xxs-sm px-3 py-2 font-semibold" @click="$emit('checkout')">
                    {{ $t('action.checkout') }}
                </Button>
            </div>
        </template>
    </CardForm>
</template>
