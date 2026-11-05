<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { index } from '@/routes/admin/monitoring-controls/aircons';
import { QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    filters: QueryParams;
}>();

const filters = ref(props.filters);
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
    timeout = setTimeout(() => {
        router.get(index.url(), filters.value, {
            replace: true,
        });
    }, 500);
};
</script>

<template>
    <InputSearch v-model="search" @input="load" />
</template>
