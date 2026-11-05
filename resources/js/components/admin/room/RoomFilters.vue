<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs/';
import { SelectArea } from '@/components/shared/selects';
import { Floor } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Props to receive floor options
const props = defineProps<{
    floors: Floor[];
    queryParams: Record<string, any>;
}>();

// Local reactive filter state
const search = ref(props.queryParams.search ?? '');
const floor = ref(props.queryParams?.floor ?? null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    const params: Record<string, any> = { ...props.queryParams, page: 1 };

    // If search is empty, don't include it in the params
    if (search.value.trim()) {
        params.search = search.value.trim();
    } else {
        delete params.search; // Remove search param if search is empty
    }

    // If floor is selected or null, include it in the params
    if (floor.value !== null) {
        params.floor = floor.value;
    } else {
        delete params.floor;
    }

    router.get('/admin/management/rooms', params, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
}

// Debounced or immediate filter trigger
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

// Watchers
watch(search, () => triggerFilters({ debounce: true }));
watch(floor, () => {
    // When floor is changed, apply filters with updated floor value
    triggerFilters({ debounce: false });
});
</script>

<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" />
        <div class="w-full max-w-50">
            <SelectArea :items="floors" v-model="floor" defaultValue="category.all_floor" valueKey="slug" />
        </div>
    </div>
</template>
