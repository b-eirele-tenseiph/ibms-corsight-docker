<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { Floor, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    queryParams: QueryParams;
    floors: Array<Floor>;
}>();

const params = reactive({ ...props.queryParams });

let timeout: any = null;

const updateFilters = (key: keyof QueryParams, value: any) => {
    params[key] = value ?? undefined;
    params.page = 1;

    clearTimeout(timeout);
    const delay = key === 'search' ? 500 : 0;
    timeout = setTimeout(() => router.reload({ data: params }), delay);
};
</script>

<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch class="shadow-none" :model-value="params.search" @update:model-value="(val) => updateFilters('search', val)" />
        <div class="w-full max-w-50">
            <SelectArea
                :items="floors"
                :model-value="params.floor ?? null"
                @update:model-value="(val) => updateFilters('floor', val)"
                defaultValue="category.all_floor"
                valueKey="slug"
            />
        </div>
    </div>
</template>
