<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { Label } from '@/components/ui/label';
import type { ClimateFilters, ClimateRoom } from '@/types';
import dayjs from 'dayjs';
import { computed } from 'vue';

const props = defineProps<{
    rooms: ClimateRoom[];
    filters: ClimateFilters;
}>();

const emit = defineEmits<{
    (e: 'change', next: Partial<ClimateFilters>): void;
}>();

const roomAreas = computed(() => props.rooms.find((room) => String(room.id) === props.filters.room)?.room_areas ?? []);

const lastUplink = computed(() => roomAreas.value.find((area) => String(area.id) === props.filters.room_area)?.last_uplink ?? null);

const onRoomChange = (value: string | number) => {
    const nextRoom = String(value);
    const nextAreas = props.rooms.find((room) => String(room.id) === nextRoom)?.room_areas ?? [];

    emit('change', { room: nextRoom, room_area: String(nextAreas[0]?.id ?? '') });
};

const onRoomAreaChange = (value: string | number) => {
    emit('change', { room_area: String(value) });
};
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-1">
        <!-- Last uplink label -->
        <div class="inline-flex items-center">
            <span
                :class="[
                    'me-1 size-2 rounded-full',
                    !lastUplink ? 'bg-gray-500' : dayjs().diff(dayjs(lastUplink), 'minute') >= 30 ? 'bg-red-500' : 'bg-green-500',
                ]"
            ></span>
            <span class="fluid-text-xs-sm font-bold">
                {{ $t('page.climate_analytics.label.last_uplink') }}:
                {{ lastUplink ? dayjs(lastUplink).format('MMM D, YYYY h:mm A') : $t('state.no_uplink') }}
            </span>
        </div>

        <!-- Rooms and areas -->
        <div class="flex w-full flex-row gap-2 sm:flex-1 sm:gap-x-3 lg:w-[364px] lg:flex-none">
            <div class="flex flex-1 flex-col gap-1">
                <Label class="fluid-text-xs-sm text-secondary-text font-semibold">
                    {{ $t('page.climate_analytics.label.room') }}
                </Label>
                <SelectArea
                    :model-value="filters.room"
                    placeholder="filter.select_rooms"
                    :items="rooms"
                    item-key="number"
                    @update:model-value="onRoomChange"
                />
            </div>
            <div class="flex flex-1 flex-col gap-1">
                <Label class="fluid-text-xs-sm text-secondary-text font-semibold">
                    {{ $t('page.climate_analytics.label.room_area') }}
                </Label>
                <SelectArea
                    :model-value="filters.room_area"
                    placeholder="filter.select_areas"
                    :items="roomAreas"
                    @update:model-value="onRoomAreaChange"
                />
            </div>
        </div>
    </div>
</template>
