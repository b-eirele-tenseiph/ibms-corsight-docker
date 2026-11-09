<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { CorsightDwellTime } from '@/types';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import { computed } from 'vue';

const props = defineProps<{
    data: CorsightDwellTime[];
    average: number;
    isLoading: boolean;
}>();

const formatDuration = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remaining = seconds % 60;
    const mm = String(minutes).padStart(2, '0');
    const ss = String(remaining).padStart(2, '0');
    return hours > 0 ? `${hours}:${mm}:${ss}` : `${mm}:${ss}`;
};

const selectedRoom = computed(() => (props.data.length === 1 ? props.data[0] : null));
</script>

<template>
    <Card class="h-full gap-2 py-4">
        <div class="flex w-full flex-col px-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.cctv.header.dwell_time') }}</span>
            <span class="fluid-text-xs-sm text-secondary-text font-semibold">{{ $t('page.admin_dashboard.cctv.header.dwell_time_subtitle') }}</span>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="data?.length > 0" class="flex h-full flex-col gap-2 px-4">
                <div class="bg-primary/5 border-primary/15 flex flex-col gap-1 rounded-xl border px-4 py-3">
                    <span class="text-secondary-text text-xs font-semibold tracking-wide uppercase">{{
                        $t('page.admin_dashboard.cctv.label.average_dwell')
                    }}</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-primary-text text-3xl leading-none font-bold">{{ formatDuration(average) }}</span>
                        <span class="text-secondary-text text-sm font-semibold">
                            {{ $t('page.admin_dashboard.cctv.label.seconds') }}
                        </span>
                    </div>
                    <div class="text-success flex items-center gap-2 text-xs font-semibold">
                        <span class="bg-success size-1.5 rounded-full" />
                        <span v-if="selectedRoom">
                            {{ $t('page.admin_dashboard.cctv.label.room_monitored', { room: selectedRoom.room }) }}
                        </span>
                        <span v-else>
                            {{ $t('page.admin_dashboard.cctv.label.rooms_monitored', { count: data.length }) }}
                        </span>
                    </div>
                </div>

                <!-- Single room detail (when a location is selected) -->
                <div v-if="selectedRoom" class="flex flex-col overflow-hidden rounded-xl border">
                    <div
                        v-for="(value, key) in {
                            location: selectedRoom.room,
                            start: dayjs(selectedRoom.start).format('YYYY-MM-DD HH:mm:ss'),
                            end: dayjs(selectedRoom.end).format('YYYY-MM-DD HH:mm:ss'),
                            duration: formatDuration(selectedRoom.duration_seconds),
                        }"
                        :key="key"
                        class="flex items-center justify-between border-b py-2 pr-4 text-xs last:border-0"
                    >
                        <span class="text-secondary-text ml-3 font-medium">{{ $t(`page.admin_dashboard.cctv.label.${key}`) }}</span>
                        <span class="font-semibold">{{ value }}</span>
                    </div>
                </div>

                <!-- Per-room list (when showing all locations) -->
                <div v-else class="h-full overflow-hidden">
                    <ScrollArea class="h-full">
                        <div
                            v-for="(entry, index) in data"
                            :key="index"
                            class="flex items-center justify-between gap-2 border-b py-2 last:border-0 last:pb-0"
                        >
                            <div class="flex w-full items-center gap-2">
                                <Icon icon="mdi:home-variant" class="text-secondary-text size-4 shrink-0" />
                                <span class="fluid-text-xs-sm font-medium">{{ entry.room }}</span>
                            </div>
                            <div class="flex shrink-0 items-baseline gap-1">
                                <span class="text-primary-text fluid-text-xs-base font-bold">
                                    {{ formatDuration(entry.average_dwell_seconds) }}
                                </span>
                                <span class="text-secondary-text text-xs font-medium"> ({{ entry.average_dwell_seconds }}s) </span>
                            </div>
                        </div>
                    </ScrollArea>
                </div>
            </div>
            <div v-else class="py-10">
                <StateEmpty label="page.admin_dashboard.cctv.label.no_dwell_data" />
            </div>
        </template>
    </Card>
</template>
