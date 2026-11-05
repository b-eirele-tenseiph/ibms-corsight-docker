<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { index } from '@/routes/admin/management/settings/scenes';
import { Room } from '@/types/t-room';
import { router } from '@inertiajs/vue3';
import { objectOmit, useDebounce } from '@vueuse/core';
import { ref, watch } from 'vue';

const props = defineProps<{
    filters: Record<string, any>;
    rooms: Room[];
}>();

const roomFilter = ref((props.filters as any)?.roomFilter ?? null);
const debouncedRoomFilter = useDebounce(roomFilter, 500);

watch(debouncedRoomFilter, (val) => {
    router.get(
        index.url(),
        {
            ...objectOmit(props.filters as any, ['page']),
            roomFilter: val === 'all' ? null : val,
            search: props.filters.search,
        },
        {
            preserveState: false,
            replace: true,
        },
    );
});
</script>
<template>
    <div class="w-full max-w-50">
        <SelectArea v-model="roomFilter" placeholder="category.all_room" :items="rooms" defaultValue="category.all_room" value-key="id" />
    </div>
</template>
