<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps<{
    data: Record<string, any>;
}>();

const filters = props.data;

const search = reactive({
    query: computed<string>({
        get: () => filters.search ?? '',
        set: (newValue) => {
            if (newValue) {
                filters.search = newValue;
            } else {
                filters.search = undefined;
            }
        },
    }),
    timeout: null as any,
    load: () => {
        clearTimeout(search.timeout);

        search.timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                {
                    ...filters,
                    page: 1,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                },
            );
        }, 500);
    },
});
</script>

<template>
    <InputSearch v-model="search.query" @input="search.load" />
</template>
