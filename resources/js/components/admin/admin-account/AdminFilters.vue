<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { index } from '@/routes/admin/management/accounts/admins';
import { Role } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    roles: Role[];
    queryParams: Record<string, any>;
}>();

const search = ref(props.queryParams.search ?? '');

const role = ref(typeof props.queryParams?.role === 'string' ? Number(props.queryParams.role) : (props.queryParams?.role ?? null));

function applyFilters() {
    const params: Record<string, any> = { ...props.queryParams, page: 1 };

    // If search is empty, don't include it in the params
    if (search.value.trim()) {
        params.search = search.value.trim();
    } else {
        delete params.search; // Remove search param if search is empty
    }
    // If floor is selected or null, include it in the params
    if (role.value !== null) {
        params.role = role.value;
    } else {
        delete params.role;
    }

    router.get(index.url(), params, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    });
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

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
</script>

<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <!-- Search Input -->
        <InputSearch v-model="search" @update:modelValue="triggerFilters({ debounce: true })" />
        <!-- Role Select -->
        <div class="w-full max-w-50">
            <SelectArea
                :items="roles"
                v-model="role"
                @update:modelValue="triggerFilters({ debounce: true })"
                defaultValue="category.all_role"
                valueKey="id"
            />
        </div>
    </div>
</template>
