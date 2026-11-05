<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { Paginate } from '@/components/shared/pagination';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useElectricMeters } from '@/composables/useElectricMeters';
import { LocationWithMeter } from '@/types';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc.js';
import { onMounted, reactive, watch } from 'vue';
import { ElectricMeterCard, ElectricMeterCardLocationFilter, ElectricMeterCardSearch } from '..';

dayjs.extend(timezone);
dayjs.extend(utc);

defineProps<{
    location: LocationWithMeter[];
}>();

const filters = reactive({ search: '', location: '' });

const { paginatedMeters, isLoading, fetchMeters } = useElectricMeters();

watch(filters, () => fetchMeters(true, filters));

onMounted(() => {
    fetchMeters();
});
</script>

<template>
    <Card class="flex h-full flex-col gap-2">
        <div class="mb-4 flex w-full flex-col gap-4 px-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col">
                <span class="fluid-text-base-lg font-bold">{{ $t('page.admin_dashboard.header.all_electric_meter') }}</span>
                <span class="fluid-text-xs-sm text-secondary-text font-medium">
                    {{ $t('page.admin_dashboard.header.all_electric_meter_subtitle') }}
                </span>
            </div>
            <div class="flex w-full gap-2 sm:w-auto">
                <ElectricMeterCardSearch v-model="filters.search" />
                <ElectricMeterCardLocationFilter :query="filters" :item="location" />
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden px-1">
            <ScrollArea class="min-h-0 flex-1 px-1 py-2">
                <template v-if="isLoading">
                    <div class="flex h-full items-center justify-center">
                        <SpinnerIcon />
                    </div>
                </template>
                <div v-else-if="!paginatedMeters?.data?.length" class="flex h-full items-center justify-center py-10">
                    <StateEmpty />
                </div>
                <div v-else class="grid grid-cols-1 gap-4 px-2 pb-1 sm:grid-cols-2 lg:grid-cols-3">
                    <template v-for="(meter, index) in paginatedMeters?.data" :key="index">
                        <ElectricMeterCard :meter />
                    </template>
                </div>
            </ScrollArea>
            <Paginate :paginate="paginatedMeters" class="px-4" />
        </div>
    </Card>
</template>
