<script setup lang="ts">
import { StateEmpty } from '@/components/shared/states';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Food, FoodCategory } from '@/types';
import { RestaurantFoodMenuItem } from '.';

const props = defineProps<{
    foods: Food[];
    foodCategories: FoodCategory[];
    cart: { food: Food; quantity: number }[];
}>();

const emit = defineEmits<{
    (e: 'update-cart', payload: { food: Food; quantity: number }): void;
}>();

const defaultTab = props.foodCategories.length > 0 ? props.foodCategories[0].id.toString() : '';
</script>

<template>
    <Tabs :default-value="defaultTab" class="">
        <ScrollArea class="w-full">
            <TabsList class="bg-card sticky top-0 z-10 flex cursor-pointer gap-2.5">
                <TabsTrigger
                    v-for="foodCategory in foodCategories"
                    :key="foodCategory.id"
                    :value="foodCategory.id.toString()"
                    class="data-[state=active]:bg-primary dark:data-[state=active]:bg-primary border-border rounded-md border px-3 py-1 shadow transition data-[state=active]:border-0 data-[state=active]:text-white"
                >
                    {{ foodCategory.name }}
                </TabsTrigger>
            </TabsList>
            <ScrollBar orientation="horizontal" />
        </ScrollArea>

        <div class="mt-3 flex-1 overflow-y-auto">
            <TabsContent
                class="4xl:h-[85svh] h-[50svh] md:h-[85svh] lg:h-[82svh] 2xl:h-[83svh]"
                v-for="foodCategory in foodCategories"
                :key="foodCategory.id"
                :value="foodCategory.id.toString()"
            >
                <div
                    v-if="foods.filter((f) => f.category_id === foodCategory.id).length > 0"
                    class="xs:grid-cols-3 4xl:grid-cols-5 grid grid-cols-2 gap-2 md:grid-cols-2 md:gap-3 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <RestaurantFoodMenuItem
                        v-for="food in foods.filter((f) => f.category_id === foodCategory.id)"
                        :key="food.id"
                        :food="food"
                        :quantity="props.cart.find((i) => i.food.id === food.id)?.quantity || 0"
                        @increment="() => emit('update-cart', { food, quantity: (props.cart.find((i) => i.food.id === food.id)?.quantity || 0) + 1 })"
                        @decrement="() => emit('update-cart', { food, quantity: (props.cart.find((i) => i.food.id === food.id)?.quantity || 0) - 1 })"
                        @update-quantity="({ food, quantity }) => emit('update-cart', { food, quantity })"
                    />
                </div>

                <!-- No data available message -->
                <div v-else class="flex h-full justify-center py-16"><StateEmpty /></div>
            </TabsContent>
        </div>
    </Tabs>
</template>
