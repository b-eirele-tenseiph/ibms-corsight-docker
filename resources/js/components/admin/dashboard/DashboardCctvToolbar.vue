<script setup lang="ts">
import { DropdownFilterSelection } from '@/components/shared/dropdowns';
import { InputComboBoxMultiSelect } from '@/components/shared/inputs';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CctvTimeRange, CorsightDeviceOption, CorsightRoom } from '@/types';
import dayjs from 'dayjs';
import { computed, ref } from 'vue';

withDefaults(
    defineProps<{
        isLive: boolean;
        lastUpdated: string | null;
        rooms: CorsightRoom[];
        devices: CorsightDeviceOption[];
    }>(),
    {
        lastUpdated: null,
        rooms: () => [],
        devices: () => [],
    },
);

// Empty arrays mean "all" (no filter applied) — the dashboard re-filters live.
const selectedLocation = defineModel<string[]>('selectedLocation', { default: () => [] });
const selectedDevices = defineModel<number[]>('selectedDevices', { default: () => [] });
const selectedRange = defineModel<CctvTimeRange>('selectedRange', { default: 'today' });

const filterOpen = ref(false);

// "Today" is the default range — it only counts as an active filter once
// the user explicitly picks 7d/30d.
const activeFilterCount = computed(
    () => (selectedLocation.value.length > 0 ? 1 : 0) + (selectedDevices.value.length > 0 ? 1 : 0) + (selectedRange.value !== 'today' ? 1 : 0),
);

const resetFilters = () => {
    selectedLocation.value = [];
    selectedDevices.value = [];
    selectedRange.value = 'today';
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2 pr-2">
        <span
            :class="[
                'flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-bold',
                isLive ? 'border-success/20 bg-success/10 text-success' : 'border-destructive/20 bg-destructive/10 text-destructive',
            ]"
        >
            <span :class="isLive ? 'bg-success size-2 animate-pulse rounded-full' : 'bg-destructive size-2 rounded-full'" />
            {{ $t(isLive ? 'page.admin_dashboard.cctv.label.live' : 'page.admin_dashboard.cctv.label.disconnected') }}
        </span>

        <span v-if="lastUpdated" class="text-secondary-muted-text hidden text-xs font-medium md:inline">
            {{ $t('page.admin_dashboard.cctv.label.last_updated', { time: dayjs(lastUpdated).format('MMM D, YYYY h:mm A') }) }}
        </span>

        <DropdownFilterSelection
            v-model:open="filterOpen"
            :count="activeFilterCount"
            :has-add-button="false"
            class="w-fit"
            bodyClass="max-h-60"
            @reset="resetFilters"
        >
            <div class="flex flex-col space-y-3">
                <InputComboBoxMultiSelect
                    v-model="selectedLocation"
                    :items="rooms"
                    label="page.admin_dashboard.cctv.label.location"
                    placeholder="category.all_location"
                    value-key="name"
                    item-key="name"
                />

                <InputComboBoxMultiSelect
                    v-model="selectedDevices"
                    :items="devices"
                    label="page.admin_dashboard.cctv.filter.device"
                    placeholder="form.placeholder.camera"
                    value-key="id"
                    item-key="name"
                />
            </div>

            <div class="grid gap-2">
                <Label class="fluid-text-xs-sm">{{ $t('page.admin_dashboard.cctv.filter.date_range') }}</Label>
                <Select v-model="selectedRange">
                    <SelectTrigger class="focus-visible:border-ring focus-visible:ring-ring/20 w-full cursor-pointer shadow-none focus-visible:ring">
                        <SelectValue :value="selectedRange" class="fluid-text-xs-sm" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="range in ['today', 'last_7_days', 'last_30_days']" :key="range" :value="range" class="fluid-text-xs-sm">
                            {{ $t(`page.admin_dashboard.cctv.filter.${range}`) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </DropdownFilterSelection>
    </div>
</template>
