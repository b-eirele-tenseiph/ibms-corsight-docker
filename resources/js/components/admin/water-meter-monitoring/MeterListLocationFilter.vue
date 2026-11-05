<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    query: any;
    item: Array<any>;
}>();

const filters = ref(props.query);

const updateRoom = (room: any) => {
    filters.value.room = room || undefined;
    filters.value.page = 1;
    router.reload({ data: props.query });
};
</script>

<template>
    <SelectArea
        :items="props.item"
        :model-value="props.query.location ?? null"
        @update:model-value="updateRoom"
        defaultValue="category.all_location"
        placeholder="category.all_location"
        valueKey="id"
        itemKey="location"
    />
</template>
