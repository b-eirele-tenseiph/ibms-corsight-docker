<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { devices } from '@/routes/admin/dashboard';
import { QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    filters: QueryParams;
    activeTab?: string;
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
        router.get(
            devices.url(),
            {
                ...filters.value,
                tab: props.activeTab,
            },
            {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: ['aircons', 'switches', 'appliances'],
            },
        );
    }, 500);
};
</script>

<template>
    <InputSearch v-model="search" @input="load" />
</template>
