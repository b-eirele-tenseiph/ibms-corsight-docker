<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { index } from '@/routes/staff/promo_management';
import type { PromoFilterProps } from '@/types/t-promo';
import { router } from '@inertiajs/vue3';
import { objectOmit, useDebounce } from '@vueuse/core';
import { ref, watch } from 'vue';

const props = defineProps<PromoFilterProps>();

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
</script>
<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" />
    </div>
</template>
