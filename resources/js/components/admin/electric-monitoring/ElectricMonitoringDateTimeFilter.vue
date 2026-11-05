<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Menubar, MenubarContent, MenubarMenu, MenubarTrigger } from '@/components/ui/menubar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { index } from '@/routes/admin/electric-monitoring';
import { ElectricMeterProps } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { useMediaQuery } from '@vueuse/core';
import { computed, reactive, ref, watch } from 'vue';
import { ElectricMonitoringGenerateReport } from '.';

const props = defineProps<ElectricMeterProps>();

const filters = reactive({ ...props.filters });
const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

const isMediumScreen = useMediaQuery('(min-width: 768px)');

const loadWithFilters = () => {
    const queryFilters = { ...filters };
    if (queryFilters.device === 'all') {
        queryFilters.device = null;
    }
    router.get(index.url(), queryFilters, {
        preserveState: false,
        replace: true,
    });
};

const DATE: any = reactive({
    value: computed({
        get: () => {
            if (filters.start && filters.end) {
                return { start: filters.start, end: filters.end };
            }
            return filters.time ?? GRAPH_CONTROLS.filterList?.[0]?.value;
        },
        set: (newValue) => {
            if (newValue) {
                filters.time = newValue;
                delete filters.start;
                delete filters.end;
            } else {
                delete filters.time;
            }
            loadWithFilters();
        },
    }),
});

const GRAPH_CONTROLS: any = reactive({
    windowWidth: window.innerWidth,
    isMobileScreen: computed(() => GRAPH_CONTROLS.windowWidth < 768),
    initialize() {
        window.addEventListener('resize', function () {
            GRAPH_CONTROLS.windowWidth = window.innerWidth;
        });
    },
    filterList: [
        {
            text: '6_hrs',
            value: 6,
        },
        {
            text: '1_d',
            value: 24,
        },
        {
            text: '1_w',
            value: 168,
        },
        {
            text: '1_m',
            value: 720,
        },
    ],
    apply(start: any, end: any) {
        Object.assign(props.filters, { start, end });
        DATE.value = null;
        loadWithFilters();
    },
    reset() {
        delete filters['start'];
        delete filters['end'];
        DATE.value = null;
        loadWithFilters();
    },
});

const startDate = ref<DateValue | undefined>(filters.start ? parseDate(filters.start) : undefined);
const endDate = ref<DateValue | undefined>(filters.end ? parseDate(filters.end) : undefined);

function onStartDateChange(date: DateValue | undefined) {
    startDate.value = date;
    filters.start = date ? date.toString() : undefined;
    isStartPopoverOpen.value = false;
}

function onEndDateChange(date: DateValue | undefined) {
    endDate.value = date;
    filters.end = date ? date.toString() : undefined;
    isEndPopoverOpen.value = false;
}

const selectedValue = ref(DATE.value);

watch(selectedValue, (newVal) => {
    DATE.value = newVal;
});
</script>

<template>
    <Menubar class="border-0 bg-transparent">
        <template v-if="isMediumScreen">
            <MenubarMenu v-for="(filter, index) in GRAPH_CONTROLS.filterList" :key="index">
                <MenubarTrigger
                    @click="DATE.value = filter.value"
                    :class="[
                        'fluid-text-xs-sm cursor-pointer px-2 transition-all duration-200 disabled:opacity-75',
                        DATE.value == filter.value ? 'decoration-primary font-bold underline decoration-2 underline-offset-13' : '',
                    ]"
                >
                    {{ $t(`date_range_filter.${filter.text}`) }}
                </MenubarTrigger>
            </MenubarMenu>
        </template>

        <template v-else>
            <Select v-model="selectedValue">
                <SelectTrigger
                    class="ring:outline-none fluid-text-xs-sm dark:bg-input/0 dark:hover:bg-input/0 h-9 w-fit border-0 border-none px-2 hover:bg-transparent focus:bg-transparent focus:ring-0"
                >
                    <Icon icon="lucide:clock" class="text-secondary-text size-4" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="filter in GRAPH_CONTROLS.filterList" :key="filter.value" :value="filter.value" class="fluid-text-xs-sm">
                        {{ $t(`date_range_filter.${filter.text}`) }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </template>

        <MenubarMenu>
            <MenubarTrigger class="fluid-text-xs-sm cursor-pointer gap-2 px-2">
                <Icon v-if="!isMediumScreen" icon="lucide:calendar-days" class="text-secondary-text size-4" />
                <span v-else> {{ $t(`date_range_filter.custom`) }}</span>
            </MenubarTrigger>

            <MenubarContent align="end" class="p-2">
                <div class="fluid-text-xs-sm mt-2 space-y-2">
                    <Label class="font-semibold">{{ $t('date_range_filter.start_date') }}</Label>

                    <Popover v-model:open="isStartPopoverOpen">
                        <PopoverTrigger as-child>
                            <button class="flex w-full items-center justify-between rounded-lg border p-2">
                                <span>{{ startDate ? startDate.toString() : $t('form.placeholder.start_date') }}</span>
                                <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar
                                :v-model="filters.start"
                                @update:model-value="onStartDateChange"
                                :max-value="today(getLocalTimeZone())"
                                calendar-label="Start Date"
                            />
                        </PopoverContent>
                    </Popover>

                    <label class="font-semibold">{{ $t('date_range_filter.end_date') }}</label>
                    <Popover v-model:open="isEndPopoverOpen">
                        <PopoverTrigger as-child>
                            <button class="flex w-full items-center justify-between rounded-lg border p-2">
                                <span>{{ endDate ? endDate.toString() : $t('form.placeholder.end_date') }}</span>
                                <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar
                                :v-model="filters.end"
                                @update:model-value="onEndDateChange"
                                :max-value="today(getLocalTimeZone())"
                                calendar-label="End Date"
                            />
                        </PopoverContent>
                    </Popover>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-2">
                    <Button
                        variant="link"
                        class="text-primary-link-text cursor fluid-text-xs-sm cursor-pointer px-1"
                        @click.prevent="GRAPH_CONTROLS.reset"
                        >{{ $t('action.reset') }}</Button
                    >
                    <Button
                        variant="link"
                        class="text-primary-link-text fluid-text-xs-sm cursor-pointer px-1"
                        @click.prevent="GRAPH_CONTROLS.apply"
                        >{{ $t('action.apply') }}</Button
                    >
                </div>
            </MenubarContent>
        </MenubarMenu>

        <ElectricMonitoringGenerateReport :electric-meter="props.electric_meter" />
    </Menubar>
</template>
