<script setup lang="ts">
import { QueryParams, Room } from '@/types';
import SwitchBotDeviceActions from './SwitchBotDeviceActions.vue';
import SwitchBotDeviceRoomFilter from './SwitchBotDeviceRoomFilter.vue';
import SwitchBotDeviceSearch from './SwitchBotDeviceSearch.vue';
import SwitchBotDeviceScanButton from './SwitchBotDeviceSyncButton.vue';

defineProps<{
    query: QueryParams;
    rooms: Array<Room>;
}>();
</script>

<template>
    <div class="flex flex-row justify-between gap-4">
        <div class="xs:flex-row flex w-full flex-col gap-2">
            <!-- Search Input -->
            <SwitchBotDeviceSearch :query />

            <!-- Room Filter -->
            <SwitchBotDeviceRoomFilter :rooms :query />
        </div>

        <!-- Scan Button -->
        <div class="flex gap-x-2">
            <SwitchBotDeviceScanButton />

            <SwitchBotDeviceActions />
        </div>
    </div>
</template>
