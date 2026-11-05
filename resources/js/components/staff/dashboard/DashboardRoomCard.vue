<script setup lang="ts">
import type { Room } from '@/types/t-room';

defineProps<{
    rooms: Room[];
}>();

const statusColors: Record<string, string> = {
    available: 'rgba(16, 185, 129, 0.25)',
    'check in': 'rgba(30, 136, 229, 0.25)',
    occupied: 'rgba(255, 99, 132, 0.25)',
    'check out': 'rgba(249, 115, 22, 0.25)',
};
</script>

<template>
    <div class="grid cursor-pointer grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        <div
            v-for="room in rooms"
            :key="room.id"
            @click="$emit('clickRoom', room)"
            class="text-primary-text flex h-[150px] flex-col justify-center rounded-md p-4 text-center shadow-sm"
            :style="{ backgroundColor: statusColors[room.status] || '#f1f5f9' }"
        >
            <div class="text-lg font-semibold">{{ room.number }}</div>
            <div class="text-sm">{{ room.name }}</div>
        </div>
    </div>
</template>
