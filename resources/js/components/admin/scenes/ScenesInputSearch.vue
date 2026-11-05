<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
const props = defineProps<{
    filters: Record<string, any>;
}>();

const filters = ref(props.filters);
let timeout: any = null;

const search = computed<string | number | undefined>({
    get: () => filters.value.search ?? null,
    set: (newValue) => {
        if (newValue) {
            filters.value.search = newValue;
        } else {
            filters.value.search = undefined;
        }
        filters.value.page = 1;
    },
});

const load = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => router.reload({ data: filters.value }), 500);
};
</script>

<template>
    <InputSearch v-model="search" @update:model-value="load" />
</template>
