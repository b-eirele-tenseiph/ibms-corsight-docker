<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs/';
import { SelectArea } from '@/components/shared/selects';
import type { FoodCategory } from '@/types/t-food';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type QueryParams = {
    search?: string;
    category?: string | number | null;
};
const props = defineProps<{
    categories: FoodCategory[];
    queryParams: QueryParams;
}>();
function toNumberOrNull(v: unknown): number | null {
    if (v === undefined || v === null || v === '') return null;
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
}
const search = ref<string>(props.queryParams.search ?? '');
const category = ref<number | null>(toNumberOrNull(props.queryParams.category));
watch(
    () => props.queryParams,
    (qp) => {
        search.value = qp.search ?? '';
        category.value = toNumberOrNull(qp.category);
    },
    { deep: true },
);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function applyFilters() {
    const params: Record<string, any> = {
        ...props.queryParams,
        page: 1,
    };
    // search
    if (search.value.trim()) params.search = search.value.trim();
    else delete params.search;
    // category (keep it numeric locally so select shows correctly)
    if (category.value !== null) params.category = category.value;
    else delete params.category;
    router.get('/crew/food-menu', params, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
}
function triggerFilters({ debounce = false } = {}) {
    if (debounce) {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
            searchTimeout = null;
        }, 500);
    } else {
        if (searchTimeout) clearTimeout(searchTimeout);
        applyFilters();
    }
}
watch(search, () => triggerFilters({ debounce: true }));
watch(category, () => triggerFilters({ debounce: false }));
</script>

<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" />

        <div class="w-full max-w-50">
            <SelectArea :items="categories" v-model="category" valueKey="id" itemKey="name" defaultValue="category.all_category" clearable />
        </div>
    </div>
</template>
