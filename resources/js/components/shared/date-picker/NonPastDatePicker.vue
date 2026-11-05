<script setup lang="ts">
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
} from '@/components/ui/calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { type DateValue, getLocalTimeZone, today } from '@internationalized/date';
import { reactiveOmit, useVModel } from '@vueuse/core';
import { CalendarRoot, type CalendarRootEmits, type CalendarRootProps, useDateFormatter, useForwardPropsEmits } from 'reka-ui';
import { createDecade, createYear, toDate } from 'reka-ui/date';
import { type HTMLAttributes, type Ref } from 'vue';

// Define props with defaults for flexibility and reuse
const props = withDefaults(defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>(), {
    modelValue: undefined,
    placeholder() {
        return today(getLocalTimeZone());
    },
    weekdayFormat: 'short',
});

// Emit model update events
const emits = defineEmits<CalendarRootEmits>();

// Extract all props except class and placeholder to forward to CalendarRoot
const delegatedProps = reactiveOmit(props, ['class', 'placeholder']);

// Bind to external modelValue using placeholder and v-model
const placeholder = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: today(getLocalTimeZone()),
}) as Ref<DateValue>;

// Forward remaining props and emits to CalendarRoot component
const forwarded = useForwardPropsEmits(delegatedProps, emits);

// Formatter for displaying readable month names
const formatter = useDateFormatter('en');

// Current date reference for comparison
const currentDate = today(getLocalTimeZone());

// Disable selection of past dates.
const isPastDate = (date: DateValue) => {
    if (date.year < currentDate.year) return true;
    if (date.year === currentDate.year && date.month < currentDate.month) return true;
    if (date.year === currentDate.year && date.month === currentDate.month && date.day < currentDate.day) return true;

    return false;
};
</script>

<template>
    <!-- Root calendar context that manages state, placeholder, and rendering -->
    <CalendarRoot
        v-slot="{ date, grid, weekDays }"
        v-model:placeholder="placeholder"
        v-bind="forwarded"
        :class="cn('rounded-md border p-3', props.class)"
    >
        <!-- Calendar heading with dropdowns for month and year -->
        <CalendarHeader>
            <CalendarHeading class="flex w-full items-center justify-between gap-2">
                <!-- Month selection dropdown -->
                <Select
                    :default-value="placeholder?.month?.toString() ?? ''"
                    @update:model-value="
                        (v) => {
                            if (!v || !placeholder?.set) return;
                            if (Number(v) === placeholder.month) return;
                            placeholder = placeholder.set({ month: Number(v) });
                        }
                    "
                >
                    <SelectTrigger aria-label="Select month" class="w-[60%]">
                        <SelectValue placeholder="Select month" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[200px]">
                        <SelectItem v-for="month in createYear({ dateObj: date })" :key="month.toString()" :value="month.month.toString()">
                            {{ formatter.custom(toDate(month), { month: 'long' }) }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Year selection dropdown -->
                <Select
                    :default-value="placeholder?.year?.toString() ?? ''"
                    @update:model-value="
                        (v) => {
                            if (!v || !placeholder?.set) return;
                            if (Number(v) === placeholder.year) return;
                            placeholder = placeholder.set({ year: Number(v) });
                        }
                    "
                >
                    <SelectTrigger aria-label="Select year" class="w-[40%]">
                        <SelectValue placeholder="Select year" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[200px]">
                        <SelectItem
                            v-for="yearValue in createDecade({ dateObj: date, startIndex: -10, endIndex: 10 })"
                            :key="yearValue.toString()"
                            :value="yearValue.year.toString()"
                        >
                            {{ yearValue.year }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </CalendarHeading>
        </CalendarHeader>

        <!-- Calendar grid(s) for month(s) -->
        <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
            <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                <!-- Header row with weekdays -->
                <CalendarGridHead>
                    <CalendarGridRow>
                        <CalendarHeadCell v-for="day in weekDays" :key="day">
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>

                <!-- Dates in month view -->
                <CalendarGridBody class="grid">
                    <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
                        <!-- Each calendar cell (date) -->
                        <CalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                            <!-- Trigger to select a date, disabled if it's in the past -->
                            <CalendarCellTrigger :day="weekDate" :month="month.value" :disabled="isPastDate(weekDate)" />
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
