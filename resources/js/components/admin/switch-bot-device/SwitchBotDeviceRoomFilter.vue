<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { QueryParams, Room } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    query: QueryParams;
    rooms: Array<Room>;
}>();

const filters = ref(props.query);

const updateRoom = (room: any) => {
    filters.value.room = room || undefined;
    filters.value.page = 1;
    router.reload({ data: props.query });
};
</script>
<template>
    <div class="w-full max-w-50">
        <SelectArea
            :items="props.rooms"
            :model-value="props.query.room ?? null"
            @update:model-value="updateRoom"
            defaultValue="category.all_room"
            valueKey="slug"
        />
    </div>
</template>
