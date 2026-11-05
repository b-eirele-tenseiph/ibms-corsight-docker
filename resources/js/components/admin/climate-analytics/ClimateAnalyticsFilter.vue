<script setup lang="ts">
import { index as climateAnalyticsIndex } from '@/routes/admin/climate-analytics';
import type { ClimateFilters, ClimateRoom } from '@/types';
import { router } from '@inertiajs/vue3';
import ClimateAnalyticsDateTimeFilter from './ClimateAnalyticsDateTimeFilter.vue';
import ClimateAnalyticsRoomFilter from './ClimateAnalyticsRoomFilter.vue';

const props = defineProps<{
    rooms: ClimateRoom[];
    filters: ClimateFilters;
}>();

const visit = (next: Partial<ClimateFilters>) => {
    router.get(
        climateAnalyticsIndex.url(),
        {
            time: next.time ?? null,
            start: next.start ?? null,
            end: next.end ?? null,
            room: next.room ?? props.filters.room,
            room_area: next.room_area ?? props.filters.room_area,
        },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-x-6">
        <ClimateAnalyticsRoomFilter :rooms="rooms" :filters="filters" @change="visit" />
        <ClimateAnalyticsDateTimeFilter :filters="filters" @change="visit" />
    </div>
</template>
