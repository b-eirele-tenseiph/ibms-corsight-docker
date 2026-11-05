<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { index } from '@/routes/staff/amenity_management';
import { AmenityCategory } from '@/types';
import { router } from '@inertiajs/vue3';
import { objectOmit, useDebounce } from '@vueuse/core';
import { ref, watch } from 'vue';

const props = defineProps<{
    queryParams?: object;
    amenityCategories: AmenityCategory[];
}>();

const search = ref((props.queryParams as any).search ?? '');
const debouncedSearch = useDebounce(search, 500);

watch(debouncedSearch, (val) => {
    router.get(
        index.url(),
        {
            ...objectOmit(props.queryParams as any, ['page']),
            search: val,
        },
        {
            preserveState: false,
            replace: true,
        },
    );
});

const categoryFilter = ref((props.queryParams as any).categoryFilter ?? null);
const debouncedCategoryFilter = useDebounce(categoryFilter, 500);

watch(debouncedCategoryFilter, (val) => {
    router.get(
        index.url(),
        {
            ...objectOmit(props.queryParams as any, ['page']),

            categoryFilter: val === 'All Categories' ? null : val,
        },
        {
            preserveState: false,
            replace: true,
        },
    );
});
</script>
<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" />
        <div class="w-full max-w-50">
            <SelectArea
                v-model="categoryFilter"
                placeholder="category.all_category"
                :items="amenityCategories"
                defaultValue="category.all_category"
                valueKey="slug"
            />
        </div>
    </div>
</template>
