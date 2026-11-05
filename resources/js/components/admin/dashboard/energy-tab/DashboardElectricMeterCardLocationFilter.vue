<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { LocationWithMeter } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    query: any;
    item: Array<LocationWithMeter>;
}>();

const filters = ref(props.query);

const updateLocation = (location: any) => {
    filters.value.location = location || undefined;
    filters.value.page = 1;
    router.reload({ data: props.query });
};
</script>

<template>
    <SelectArea
        :items="props.item"
        :model-value="props.query.location ?? null"
        @update:model-value="updateLocation"
        defaultValue="category.all_location"
        placeholder="category.all_location"
        valueKey="id"
        itemKey="name"
    />
</template>
