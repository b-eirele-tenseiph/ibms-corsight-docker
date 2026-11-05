<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { history } from '@/routes/crew/orders';
import { router } from '@inertiajs/vue3';
import { objectOmit, useDebounce } from '@vueuse/core';
import { ref, watch } from 'vue';

const props = defineProps<{
    queryParams?: object;
}>();

const search = ref((props.queryParams as any).search ?? '');
const debouncedSearch = useDebounce(search, 500);

watch(debouncedSearch, (val) => {
    router.get(
        history.url(),
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
</script>
<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" />
    </div>
</template>
