<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { AdminPageLayout } from '@/layouts/admin';
import { DateOption } from '@/types';
import { ref } from 'vue';
import { AirconGraphPanelFilter, AirconTemperatureGraph } from '.';

const props = defineProps<{
    airconList: Array<any>;
}>();

const selectedAircon = ref<string | number | null>(props.airconList.length > 0 ? props.airconList[0].id : null);
const selectedDay = ref<string>('1');

const days: DateOption[] = [
    { value: '1', label: 'today' },
    { value: '2', label: 'last_2_days' },
    { value: '7', label: 'last_7_days' },
];
</script>

<template>
    <AdminPageLayout class="mt-2 mb-12 flex h-[50vh] flex-col">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="fluid-text-sm-base font-bold">
                {{ $t('head.aircon_temperature') }}
            </h1>
            <div class="flex flex-row gap-2">
                <div class="w-full max-w-50">
                    <SelectArea v-model="selectedAircon" :items="airconList" placeholder="filter.select_aircon" valueKey="id" />
                </div>
                <AirconGraphPanelFilter :days="days" v-model="selectedDay" />
            </div>
        </div>
        <AirconTemperatureGraph :device="selectedAircon" :filter="selectedDay" />
    </AdminPageLayout>
</template>
