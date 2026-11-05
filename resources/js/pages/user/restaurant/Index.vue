<script setup lang="ts">
import {
    RestaurantFoodMenu,
    RestaurantFoodOrderDetails,
    RestaurantFoodOrderHistorySheet,
    RestaurantFoodOrderReviewDetails,
} from '@/components/user/restaurant';
import { useModal as useSheet } from '@/composables/useModal';
import { UserPageLayout } from '@/layouts/user';
import UserLayout from '@/layouts/UserLayout.vue';
import { store } from '@/routes/user/restaurant';
import { Food, FoodCategory, RestaurantFoodOrderHistory, User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, reactive, ref, watch } from 'vue';

const props = defineProps<{
    foods: Food[];
    foodCategories: FoodCategory[];
    orders: RestaurantFoodOrderHistory[];
}>();

const orderHistoryModal = useSheet<RestaurantFoodOrderHistory>();

// Cart state lives here
const cart = ref<{ food: Food; quantity: number }[]>([]);

// This will store all confirmed orders temporarily in the browser
const orderHistory = ref(props.orders);

// View state: 'details' or 'review'
const viewState = ref<'details' | 'review'>('details');

function updateCart(payload: { food: Food; quantity: number }) {
    const { food, quantity } = payload;
    const existing = cart.value.find((i) => i.food.id === food.id);

    if (existing) {
        existing.quantity = quantity;
        if (quantity <= 0) {
            cart.value = cart.value.filter((i) => i.food.id !== food.id);
        }
    } else if (quantity > 0) {
        cart.value.push({ food, quantity });
    }
}

// Confirm modal controls
const order_panel = reactive({
    isOpen: false,
    form: useForm<any>({
        orders: '',
    }),

    clearCart() {
        cart.value = [];
    },

    handleCheckout() {
        viewState.value = 'review';
    },

    handleBack() {
        viewState.value = 'details';
    },

    confirmOrder() {
        // TODO: Send order to backend\
        order_panel.form.orders = cart.value.map(({ food, quantity }) => ({ id: food.id, quantity }));
        order_panel.form.post(store.url(), {
            onSuccess: () => {
                cart.value = [];
                viewState.value = 'details';
                order_panel.clearCart();
                orderHistoryModal.visible.value = true;
            },
            onError: () => {
                // Toast message on error
            },
        });
    },
});

// 👉 Load saved order history when component mounts
onMounted(() => {
    const savedOrders = localStorage.getItem('orderHistory');
    if (savedOrders) {
        orderHistory.value = JSON.parse(savedOrders);
    }
    const urlParams = new URLSearchParams(window.location.search);
    const triggerId = urlParams.get('order_id');

    if (triggerId) {
        orderHistoryModal.visible.value = true;
    }
});

// 👉 Save order history automatically whenever it changes
watch(
    orderHistory,
    (newOrders) => {
        localStorage.setItem('orderHistory', JSON.stringify(newOrders));
    },
    { deep: true },
);

const page = usePage<{ auth: { user: User } }>();
</script>

<template>
    <Head :title="$t('head.restaurant')" />
    <UserLayout :title="page.props.auth.user.room_name ?? ''">
        <UserPageLayout>
            <div class="grid grid-cols-1 gap-y-3 px-3 md:grid-cols-3 md:gap-x-3">
                <div class="col-span-1 md:col-span-2">
                    <RestaurantFoodMenu :foods="props.foods" :foodCategories="props.foodCategories" :cart="cart" @update-cart="updateCart" />
                </div>

                <div class="flex items-start justify-end md:col-span-1">
                    <div class="sticky flex h-[70svh] w-full justify-end md:h-[90svh]">
                        <RestaurantFoodOrderDetails
                            v-if="viewState === 'details'"
                            :cart="cart"
                            @clear-cart="order_panel.clearCart"
                            @checkout="order_panel.handleCheckout"
                            :onRead="orderHistoryModal.open"
                            :restaurant-food-order-history="orderHistoryModal.data.value"
                        />

                        <RestaurantFoodOrderReviewDetails
                            v-else
                            :cart="cart"
                            @back="order_panel.handleBack"
                            @confirm-order="order_panel.confirmOrder"
                            :onRead="orderHistoryModal.open"
                            :restaurant-food-order-history="orderHistoryModal.data.value"
                        />
                        <RestaurantFoodOrderHistorySheet :open="orderHistoryModal.visible.value" @close="orderHistoryModal.close" :orders="orders" />
                    </div>
                </div>
            </div>
        </UserPageLayout>
    </UserLayout>
</template>
