<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { MenubarCheckboxItem, MenubarContent, MenubarLabel, MenubarMenu, MenubarSeparator, MenubarTrigger } from '@/components/ui/menubar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { generate } from '@/routes/admin/electric-report';
import { ElectricMeterReportProps } from '@/types';
import { Icon } from '@iconify/vue';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { useMediaQuery } from '@vueuse/core';
import axios from 'axios';
import dayjs from 'dayjs';
import quarterOfYear from 'dayjs/plugin/quarterOfYear';
import { computed, inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';

dayjs.extend(quarterOfYear);

defineProps<ElectricMeterReportProps>();

const { t } = useI18n();
const toast = inject('toast') as Record<string, (body: string, options?: any) => void>;

const selectedMeters = ref<any[]>([]);
const startDate = ref<DateValue>();
const endDate = ref<DateValue>();
const selectedPreset = ref('custom');
const errors = ref<Record<string, string>>({});
const isGenerating = ref(false);
const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

const datePresets = [
    { name: t('date_range_filter.this_month'), value: 'thisMonth' },
    { name: t('date_range_filter.this_quarter'), value: 'thisQuarter' },
    { name: t('date_range_filter.this_year'), value: 'thisYear' },
    { name: t('date_range_filter.last_month'), value: 'lastMonth' },
    { name: t('date_range_filter.last_quarter'), value: 'lastQuarter' },
    { name: t('date_range_filter.last_year'), value: 'lastYear' },
];

const dateRanges: Record<string, [string, string]> = {
    thisMonth: ['month', 'month'],
    thisQuarter: ['quarter', 'quarter'],
    thisYear: ['year', 'year'],
    lastMonth: ['month', 'month'],
    lastQuarter: ['quarter', 'quarter'],
    lastYear: ['year', 'year'],
};

const toggleMeter = (id: any) => {
    const idx = selectedMeters.value.indexOf(id);
    if (idx > -1) {
        selectedMeters.value.splice(idx, 1);
    } else {
        selectedMeters.value.push(id);
    }
};

const setDate = (date: DateValue | undefined, isStart: boolean) => {
    if (isStart) {
        startDate.value = date;
        isStartPopoverOpen.value = false;
    } else {
        endDate.value = date;
        isEndPopoverOpen.value = false;
    }
};

const onPresetChange = (val: unknown) => {
    const preset = (val ?? 'custom') as string;
    const range = dateRanges[preset];

    if (range) {
        const now = dayjs().startOf('day');
        const isLast = preset.startsWith('last');
        const [unit] = range;

        const start = isLast ? now.subtract(1, unit as any).startOf(unit as any) : now.startOf(unit as any);
        const end = isLast ? now.subtract(1, unit as any).endOf(unit as any) : now;

        startDate.value = parseDate(start.format('YYYY-MM-DD'));
        endDate.value = parseDate(end.format('YYYY-MM-DD'));
    }
};

const generateReport = async () => {
    isGenerating.value = true;
    errors.value = {};

    try {
        await axios.get(generate.url(), {
            params: {
                start: startDate.value?.toString(),
                end: endDate.value?.toString(),
                meters: selectedMeters.value,
            },
        });

        toast.message(t('toast.header.report_progress'), {
            description: t('toast.body.report_description'),
            classes: {
                toast: 'border-l-3 border-l-info',
                description: 'fluid-text-xs-sm text-primary-text',
            },
        });
    } catch (error: any) {
        errors.value = error.response?.data?.errors || {};
    } finally {
        isGenerating.value = false;
    }
};

const resetReport = () => {
    selectedMeters.value = [];
    startDate.value = undefined;
    endDate.value = undefined;
    selectedPreset.value = 'custom';
    errors.value = {};
};

const startDateVal = computed(() => startDate.value as any);
const endDateVal = computed(() => endDate.value as any);

const isMediumScreen = useMediaQuery('(min-width: 768px)');
</script>

<template>
    <MenubarMenu>
        <MenubarTrigger class="fluid-text-xs-sm cursor-pointer gap-2 px-2">
            <Icon v-if="!isMediumScreen" icon="ci:file-document" class="text-secondary-text size-4" />
            <span v-else> {{ $t('page.electric_monitoring.label.generate_report') }}</span>
        </MenubarTrigger>

        <MenubarContent class="w-full min-w-60 p-2 sm:min-w-86" align="end">
            <MenubarLabel class="fluid-text-xs-sm pl-0">{{ $t('filter.select_meter') }}</MenubarLabel>

            <div class="max-h-[100px] overflow-y-auto pr-2">
                <MenubarCheckboxItem
                    v-for="meter in electricMeter"
                    :key="meter.id"
                    :model-value="selectedMeters.includes(meter.id)"
                    @select.prevent="toggleMeter(meter.id)"
                    class="fluid-text-xs-sm"
                >
                    {{ meter.name }}
                </MenubarCheckboxItem>
            </div>

            <p v-if="errors.meters" class="text-destructive fluid-text-xs-sm">{{ errors.meters[0] }}</p>

            <MenubarSeparator />

            <MenubarLabel class="fluid-text-xs-sm pl-0">{{ $t('filter.select_date_range') }}</MenubarLabel>

            <div class="fluid-text-xs-sm space-y-4">
                <SelectArea
                    v-model="selectedPreset"
                    @update:model-value="onPresetChange"
                    placeholder="form.placeholder.date"
                    :items="datePresets"
                    valueKey="value"
                />

                <div class="flex flex-col gap-2 sm:flex-row">
                    <!-- Start Date -->
                    <div class="flex w-full flex-col gap-2">
                        <Label class="fluid-text-xs-sm font-semibold">{{ $t('date_range_filter.start_date') }}</Label>
                        <Popover v-model:open="isStartPopoverOpen">
                            <PopoverTrigger as-child>
                                <button class="flex w-full items-center justify-between gap-2 rounded-lg border p-2">
                                    <span>{{ startDate?.toString() || $t('form.placeholder.start_date') }}</span>
                                    <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="end">
                                <Calendar
                                    :model-value="startDateVal"
                                    @update:model-value="(d) => setDate(d, true)"
                                    :max-value="today(getLocalTimeZone())"
                                />
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- End Date -->
                    <div class="flex w-full flex-col gap-2">
                        <Label class="fluid-text-xs-sm font-semibold">{{ $t('date_range_filter.end_date') }}</Label>
                        <Popover v-model:open="isEndPopoverOpen">
                            <PopoverTrigger as-child>
                                <button class="flex w-full items-center justify-between gap-2 rounded-lg border p-2">
                                    <span>{{ endDate?.toString() || $t('form.placeholder.end_date') }}</span>
                                    <Icon icon="mdi:calendar" class="text-secondary-muted-text size-4" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="end">
                                <Calendar
                                    :model-value="endDateVal"
                                    @update:model-value="(d) => setDate(d, false)"
                                    :max-value="today(getLocalTimeZone())"
                                />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <p v-if="errors.start || errors.end" class="text-destructive fluid-text-xs-sm">
                    {{ errors.start?.[0] || errors.end?.[0] }}
                </p>
            </div>

            <div class="text-primary-link-text fluid-text-xs-sm flex justify-end gap-2">
                <Button variant="link" class="px-1" @click="resetReport">{{ $t('action.reset') }}</Button>
                <Button variant="link" class="px-1" @click.prevent="generateReport" :disabled="isGenerating">
                    {{ $t('action.apply') }}
                </Button>
            </div>
        </MenubarContent>
    </MenubarMenu>
</template>
