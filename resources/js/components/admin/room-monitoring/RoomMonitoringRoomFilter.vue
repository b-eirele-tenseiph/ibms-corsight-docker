<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/room-monitoring';
import { QueryParams, RoomMonitoringRoom } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    query: QueryParams;
    item: Array<RoomMonitoringRoom>;
}>();

const { t } = useI18n();

const selectedSlug = computed(() => props.query.room ?? 'all-room');

const handleRoomArea = (slug: string | null) => {
    const params = { ...props.query, room: slug === 'all-room' ? undefined : slug };
    router.get(index.url(), params, { replace: true });
};

const rooms = computed(() => {
    const list = props.item ?? [];

    return [
        {
            name: t('category.all_room'),
            slug: 'all-room',
            active: list.reduce((a, i) => a + (i.device_status?.active ?? 0), 0),
            total: list.reduce((a, i) => a + (i.device_status?.total ?? 0), 0),
        },
        ...list.map((i) => ({
            ...i,
            active: i.device_status?.active ?? 0,
            total: i.device_status?.total ?? 0,
        })),
    ];
});
</script>

<template>
    <div
        v-for="room in rooms"
        :key="room.slug"
        :class="[
            'hover:bg-sidebar-accent mb-1 flex cursor-pointer items-center justify-between gap-2 overflow-hidden p-3 text-sm hover:rounded-md',
            { 'bg-sidebar-accent rounded-md': selectedSlug === room.slug },
        ]"
        @click="handleRoomArea(room.slug ?? null)"
    >
        <Label class="fluid-text-xs-sm w-full cursor-pointer truncate font-semibold">
            {{ room.name }}
        </Label>
        <div class="flex flex-row gap-2">
            <Label class="fluid-text-xs-sm cursor-pointer justify-end font-semibold text-nowrap"> {{ room.active }}/{{ room.total }} </Label>
            <div :class="['size-4 shrink-0 rounded-full', Number(room.active) > 0 ? 'bg-success' : 'bg-destructive']"></div>
        </div>
    </div>
</template>
