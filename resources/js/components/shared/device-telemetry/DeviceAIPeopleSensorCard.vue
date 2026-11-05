<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{
    people: Record<string, any>;
}>();

const getValue = (type: string) => props.people.latest_data?.[type]?.value || 0;
const getDailyOcc = (key: string) => props.people.latest_data?.[0]?.context?.[key] ?? 0;

const peopleCounter = computed(() => getValue('people_counter'));
const accumulatedIn = computed(() => getValue('accumulated_in'));
const accumulatedOut = computed(() => getValue('accumulated_out'));
const currentVisitors = computed(() => getDailyOcc('current_count'));
const totalVisitors = computed(() => getDailyOcc('max_count'));

const operationType = computed(() => props.people?.context?.operation);
</script>
<template>
    <Card class="xs:p-4 bg-controls-default h-full p-3">
        <CardContent class="flex h-full flex-col justify-between p-0">
            <Label class="fluid-text-xs-sm truncate leading-5 font-semibold"> {{ people.room.room_area || people.room.name }}</Label>

            <!-- Line Crossing Counter -->
            <template v-if="operationType == 'line_crossing_counter'">
                <div class="flex h-full flex-col items-center justify-center gap-1">
                    <div class="flex flex-col items-center">
                        <span class="font-bold">{{ accumulatedIn }}</span>
                        <span class="flex w-full flex-row justify-center gap-1">
                            <Label class="truncate text-xs font-normal">
                                {{ $t('device.telemetry.entry') }}
                            </Label>
                            <Icon icon="la:user" class="size-4" />
                        </span>
                    </div>
                    <hr class="bg-border h-px w-full rounded-lg border-0" />
                    <div class="flex flex-col items-center">
                        <span class="font-bold">{{ accumulatedOut }}</span>
                        <span class="flex w-full flex-row justify-center gap-1">
                            <Label class="truncate text-xs font-normal">
                                {{ $t('device.telemetry.exit') }}
                            </Label>
                            <Icon icon="la:user" class="size-4" />
                        </span>
                    </div>
                </div>
                <span class="text-secondary-text w-full truncate text-center text-xs">
                    {{ people.name }}
                </span>
            </template>

            <!-- Region People Counter -->
            <template v-else-if="operationType == 'region_people_counter'">
                <div class="flex h-full flex-1 items-center justify-center gap-2">
                    <span class="text-2xl font-bold">{{ peopleCounter }}</span>
                    <Icon icon="la:users" class="size-8" />
                </div>
                <span class="text-secondary-text w-full truncate text-center text-xs">
                    {{ people.name }}
                </span>
            </template>

            <!-- Total Visitors -->
            <template v-else>
                <div class="flex h-full w-full flex-col items-center justify-center gap-1">
                    <div class="flex w-full flex-col items-center">
                        <span class="font-bold">{{ currentVisitors }}</span>
                        <span class="flex w-full flex-row justify-center gap-1">
                            <Label class="truncate text-xs font-normal">
                                {{ $t('device.telemetry.peak_visitors') }}
                            </Label>
                            <Icon icon="la:user-friends" class="size-4" />
                        </span>
                    </div>
                    <hr class="bg-border h-px w-full rounded-lg border-0" />
                    <div class="flex w-full flex-col items-center">
                        <span class="font-bold">{{ totalVisitors }}</span>
                        <span class="flex w-full flex-row justify-center gap-1">
                            <Label class="truncate text-xs font-normal">
                                {{ $t('device.telemetry.current_visitors') }}
                            </Label>
                            <Icon icon="la:user-friends" class="size-4" />
                        </span>
                    </div>
                </div>
            </template>
        </CardContent>
    </Card>
</template>
