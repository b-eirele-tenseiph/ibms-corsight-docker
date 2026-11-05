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

// Define props with default values for placeholder and weekday format.
const props = withDefaults(defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>(), {
    modelValue: undefined,
    placeholder() {
        return today(getLocalTimeZone());
    },
    weekdayFormat: 'short',
});

// Emits events for two-way binding and interactions
const emits = defineEmits<CalendarRootEmits>();

// Extract props excluding `class` and `placeholder` for forwarding
const delegatedProps = reactiveOmit(props, ['class', 'placeholder']);

// Sets up two-way binding for placeholder date using VModel
const placeholder = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: today(getLocalTimeZone()),
}) as Ref<DateValue>;

// Forward remaining props and emits to CalendarRoot
const forwarded = useForwardPropsEmits(delegatedProps, emits);

// Setup date formatter (e.g., for displaying full month names)
const formatter = useDateFormatter('en');
</script>

<template>
    <!-- Calendar Root provides the base context for the calendar -->
    <CalendarRoot
        v-slot="{ date, grid, weekDays }"
        v-model:placeholder="placeholder"
        v-bind="forwarded"
        :class="cn('rounded-md border p-3', props.class)"
    >
        <!-- Calendar Header with dropdowns for selecting month and year -->
        <CalendarHeader>
            <CalendarHeading class="flex w-full items-center justify-between gap-2">
                <!-- Month Selector -->
                <Select
                    :default-value="placeholder.month.toString()"
                    @update:model-value="
                        (v) => {
                            if (!v || !placeholder) return;
                            if (Number(v) === placeholder?.month) return;
                            placeholder = placeholder.set({
                                month: Number(v),
                            });
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

                <!-- Year Selector -->
                <Select
                    :default-value="placeholder.year.toString()"
                    @update:model-value="
                        (v) => {
                            if (!v || !placeholder) return;
                            if (Number(v) === placeholder?.year) return;
                            placeholder = placeholder.set({
                                year: Number(v),
                            });
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

        <!-- Calendar Grid (1 to many months depending on view) -->
        <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
            <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                <!-- Weekday headers -->
                <CalendarGridHead>
                    <CalendarGridRow>
                        <CalendarHeadCell v-for="day in weekDays" :key="day">
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>

                <!-- Calendar Body (Dates) -->
                <CalendarGridBody class="grid">
                    <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
                        <!-- Each calendar day cell -->
                        <CalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                            <CalendarCellTrigger :day="weekDate" :month="month.value" />
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
