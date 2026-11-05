<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    queryParams: Record<string, any>;
    rooms: Array<any>;
}>();

const filters = ref(props.queryParams);
let timeout: any = null;

const updateRoom = (room: any) => {
    filters.value.room = room || undefined;
    filters.value.page = 1;
    router.reload({ data: props.queryParams });
};

const search = computed<string | number | undefined>({
    get: () => filters.value.search ?? null,
    set: (newValue) => {
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
    timeout = setTimeout(() => router.reload({ data: filters.value }), 500);
};
</script>
<template>
    <div class="xs:flex-row flex w-full flex-col gap-2">
        <InputSearch v-model="search" @input="load" />
        <div class="w-full max-w-50">
            <SelectArea
                :items="props.rooms"
                :model-value="props.queryParams.room ?? null"
                @update:model-value="updateRoom"
                defaultValue="category.all_room"
                valueKey="slug"
            />
        </div>
    </div>
</template>
