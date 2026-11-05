<script setup lang="ts">
import AddFoodModal from '@/components/crew/food/FoodAddModal.vue';
import DeleteFoodModal from '@/components/crew/food/FoodDeleteModal.vue';
import FoodFilters from '@/components/crew/food/FoodFilters.vue';
import FoodTable from '@/components/crew/food/FoodTable.vue';
import UpdateFoodModal from '@/components/crew/food/FoodUpdateModal.vue';
import Paginate from '@/components/shared/pagination/Paginate.vue';
import Button from '@/components/ui/button/Button.vue';
import { useModal } from '@/composables/useModal';
import AdminPageLayout from '@/layouts/admin/AdminPageLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { PaginatedResponse } from '@/types';
import type { Filters, Food, FoodCategory } from '@/types/t-food';
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, unref } from 'vue';
const props = defineProps<{
    foods: PaginatedResponse<Food>;
    categories: FoodCategory[];
    filters: Filters;
}>();

const addModal = useModal<void>();

const updateModal = useModal<Food | null>();
const deleteModal = useModal<Food | null>();

function openUpdateModal(food: Food) {
    updateModal.open(food);
}

function handleUpdateSuccess() {
    updateModal.close();
}
function openDelete(food: Food) {
    deleteModal.open(food);
}
function handleDeleteSuccess() {
    deleteModal.close();
}
// local filters state for table header + filters component
const tableFilters = ref({
    search: props.filters.search ?? '',
    category: props.filters.category ?? '',
    sort: props.filters.sort ?? 'name',
    direction: (props.filters.direction as 'asc' | 'desc') ?? 'asc',
    page: props.filters.page ?? 1,
});
const activeCategory = computed(() => {
    const id = props.filters.category;
    if (id === undefined || id === null || id === '') return null;
    const catId = typeof id === 'string' ? parseInt(id, 10) : id;
    return props.categories.find((c) => c.id === catId) ?? null;
});
const itemCount = computed(() => props.foods.data.length);
</script>

<template>
    <Head :title="$t('head.food_menu')" />
    <AdminLayout title="head.food_menu">
        <AdminPageLayout>
            <div class="flex flex-row justify-between gap-4">
                <FoodFilters :categories="props.categories" :query-params="props.filters" />

                <Button variant="default" @click="addModal.open()" class="cursor-pointer">
                    <Icon icon="lucide:plus" class="size-4" />
                    <span class="hidden md:flex">{{ $t('modal.action.add_new_food') }}</span>
                </Button>
            </div>
            <div v-if="activeCategory" class="fluid-text-xs-sm">
                <span class="text-primary-text font-medium">{{ activeCategory.name }}</span>
                <span class="text-secondary-text ml-2">{{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }}</span>
            </div>

            <FoodTable :rows="props.foods.data" v-model:filters="tableFilters" @update="openUpdateModal" @delete="openDelete" />

            <Paginate :paginate="foods" />

            <!-- Add -->
            <AddFoodModal :is-open="addModal.visible.value" :categories="props.categories" @close="addModal.close" />

            <!-- Update -->
            <UpdateFoodModal
                :is-open="unref(updateModal.visible)"
                :categories="props.categories"
                :food="unref(updateModal.data)"
                @close="updateModal.close"
                @submit="handleUpdateSuccess"
            />
            <DeleteFoodModal
                :is-open="unref(deleteModal.visible)"
                :food="unref(deleteModal.data)"
                @close="deleteModal.close"
                @submit="handleDeleteSuccess"
            />
        </AdminPageLayout>
    </AdminLayout>
</template>
