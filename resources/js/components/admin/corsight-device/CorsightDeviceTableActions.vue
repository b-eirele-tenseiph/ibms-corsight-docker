<script setup lang="ts">
import { InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { Button } from '@/components/ui/button';
import { QueryParams, Room } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    query: QueryParams;
    rooms: Array<Room>;
}>();

const filters = ref(props.query);
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
    timeout = setTimeout(() => router.reload({ data: filters.value }), 500);
};

const updateRoom = (room: any) => {
    filters.value.room = room || undefined;
    filters.value.page = 1;
    router.reload({ data: filters.value });
};
</script>
<template>
    <div class="flex flex-row justify-between gap-4">
        <div class="xs:flex-row flex w-full flex-col gap-2">
            <InputSearch v-model="search" @input="load" />
            <div class="w-full max-w-50">
                <SelectArea
                    :items="props.rooms"
                    :model-value="props.query.room ?? null"
                    @update:model-value="updateRoom"
                    defaultValue="category.all_room"
                    valueKey="slug"
                />
            </div>
        </div>
        <div class="flex flex-row justify-end gap-2">
            <Button variant="default" @click="$emit('open')">
                <Icon icon="lucide:plus" class="size-4" />
                <span class="hidden md:flex">{{ $t('modal.action.add_device') }}</span></Button
            >
        </div>
    </div>
</template>
