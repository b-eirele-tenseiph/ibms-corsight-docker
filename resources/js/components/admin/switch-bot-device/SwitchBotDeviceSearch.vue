<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    query: QueryParams;
}>();

const filters = ref(props.query);
let timeout: any = null;

const search = computed({
    get: () => filters.value.search ?? '',
    set: (newValue: string) => {
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
