<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { index } from '@/routes/admin/management/logs';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    queryParams: { search?: string; [key: string]: any };
}>();

//#region Search Function
const query = ref<{ search: string }>({
    search: (props.queryParams?.search as string) || '',
});

function applySearch() {
    const queryParams: Record<string, any> = { ...props.queryParams, page: 1 };
    const search = query.value.search.trim();
    if (search) {
        queryParams.search = search;
    } else {
        delete queryParams.search;
        delete queryParams.page;
    }
    router.get(index.url(), queryParams, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

//#endregion
</script>
<template>
    <InputSearch v-model="query.search" @change="applySearch" />
</template>
