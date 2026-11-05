<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Menubar, MenubarContent, MenubarMenu, MenubarTrigger } from '@/components/ui/menubar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import type { ClimateFilters } from '@/types';
import { Icon } from '@iconify/vue';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    filters: ClimateFilters;
}>();

const emit = defineEmits<{
    (e: 'change', next: Partial<ClimateFilters>): void;
}>();

const isMediumScreen = useMediaQuery('(min-width: 768px)');

const PRESETS = [
    { text: '6_hrs', value: 6 },
    { text: '1_d', value: 24 },
    { text: '1_w', value: 168 },
    { text: '1_m', value: 720 },
];

const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

const singleDate = (value: unknown): DateValue | undefined => (value && !Array.isArray(value) ? (value as DateValue) : undefined);

const startDate = ref<any>(props.filters.start ? parseDate(props.filters.start) : undefined);
const endDate = ref<any>(props.filters.end ? parseDate(props.filters.end) : undefined);

const startDateValue = computed(() => singleDate(startDate.value));
const endDateValue = computed(() => singleDate(endDate.value));

const isCustomRange = computed(() => Boolean(props.filters.start && props.filters.end));

// Keep the custom range pickers in sync when the filters change
watch(
    () => [props.filters.start, props.filters.end],
    ([start, end]) => {
        startDate.value = start ? parseDate(start) : undefined;
        endDate.value = end ? parseDate(end) : undefined;
    },
);

const selectedPreset = computed({
    get: () => props.filters.time ?? 24,
    set: (value: number) => applyPreset(value),
});

const applyPreset = (value: number) => {
    startDate.value = undefined;
    endDate.value = undefined;

    emit('change', { time: value, start: null, end: null });
};

const onStartDateChange = (date: unknown) => {
    startDate.value = date;
    isStartPopoverOpen.value = false;
};

const onEndDateChange = (date: unknown) => {
    endDate.value = date;
    isEndPopoverOpen.value = false;
};

const applyCustomRange = () => {
    const start = startDateValue.value;
    const end = endDateValue.value;

    if (!start || !end) {
        return;
    }

    emit('change', { time: null, start: start.toString(), end: end.toString() });
};

const resetCustomRange = () => {
    startDate.value = undefined;
    endDate.value = undefined;

    emit('change', { time: 24, start: null, end: null });
};
</script>

<template>
    <Menubar class="h-9 w-fit shrink-0 border-0 bg-transparent">
        <template v-if="isMediumScreen">
            <MenubarMenu v-for="preset in PRESETS" :key="preset.value">
                <MenubarTrigger
                    class="fluid-text-xs-sm cursor-pointer px-2 transition-all duration-200"
                    :class="
                        filters.time === preset.value && !isCustomRange
                            ? 'decoration-primary font-bold underline decoration-2 underline-offset-13'
                            : ''
                    "
                    @click="applyPreset(preset.value)"
                >
                    {{ $t(`date_range_filter.${preset.text}`) }}
                </MenubarTrigger>
            </MenubarMenu>
        </template>

        <template v-else>
            <Select v-model="selectedPreset">
                <SelectTrigger
                    class="ring:outline-none fluid-text-xs-sm dark:bg-input/0 dark:hover:bg-input/0 h-9 w-fit border-0 border-none px-2 shadow-none hover:bg-transparent focus:bg-transparent focus:ring-0"
                >
                    <Icon icon="lucide:clock" class="text-secondary-text size-4" />
                </SelectTrigger>
                <SelectContent align="end">
                    <SelectItem v-for="preset in PRESETS" :key="preset.value" :value="preset.value" class="fluid-text-xs-sm">
                        {{ $t(`date_range_filter.${preset.text}`) }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </template>

        <MenubarMenu>
            <MenubarTrigger class="fluid-text-xs-sm cursor-pointer gap-2 px-2">
                <Icon v-if="!isMediumScreen" icon="lucide:calendar-days" class="text-secondary-text size-4" />
                <span v-else>{{ $t('date_range_filter.custom') }}</span>
            </MenubarTrigger>

            <MenubarContent align="end" class="p-2">
                <div class="fluid-text-xs-sm mt-2 space-y-2">
                    <Label class="font-semibold">{{ $t('date_range_filter.start_date') }}</Label>

                    <Popover v-model:open="isStartPopoverOpen">
                        <PopoverTrigger as-child>
                            <button class="flex w-full items-center justify-between rounded-lg border p-2">
                                <span>{{ startDateValue ? startDateValue.toString() : $t('form.placeholder.start_date') }}</span>
                                <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar
                                :model-value="startDate"
                                @update:model-value="onStartDateChange"
                                :max-value="today(getLocalTimeZone())"
                                calendar-label="Start Date"
                            />
                        </PopoverContent>
                    </Popover>

                    <Label class="font-semibold">{{ $t('date_range_filter.end_date') }}</Label>

                    <Popover v-model:open="isEndPopoverOpen">
                        <PopoverTrigger as-child>
                            <button class="flex w-full items-center justify-between rounded-lg border p-2">
                                <span>{{ endDateValue ? endDateValue.toString() : $t('form.placeholder.end_date') }}</span>
                                <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar
                                :model-value="endDate"
                                @update:model-value="onEndDateChange"
                                :max-value="today(getLocalTimeZone())"
                                calendar-label="End Date"
                            />
                        </PopoverContent>
                    </Popover>
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        variant="link"
                        class="text-primary-link-text cursor fluid-text-xs-sm cursor-pointer px-1"
                        @click.prevent="resetCustomRange"
                    >
                        {{ $t('action.reset') }}
                    </Button>
                    <Button variant="link" class="text-primary-link-text fluid-text-xs-sm cursor-pointer px-1" @click.prevent="applyCustomRange">
                        {{ $t('action.apply') }}
                    </Button>
                </div>
            </MenubarContent>
        </MenubarMenu>
    </Menubar>
</template>
