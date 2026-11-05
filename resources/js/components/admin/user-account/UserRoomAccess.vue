<script setup lang="ts">
import { InputCheckboxWithLabel, InputDateAndTime } from '@/components/shared/inputs';
import { formatTime } from '@/lib/utils';
import { CalendarDate, getLocalTimeZone, now, Time } from '@internationalized/date';
import { useVModel } from '@vueuse/core';
import { nextTick, ref, watch } from 'vue';

// Define props received from the parent component
const props = defineProps<{
    isPermanent?: boolean;
    startDate: string;
    endDate: string;
    startTime: string;
    endTime: string;
    startDateError?: string;
    endDateError?: string;
    startTimeError?: string;
    endTimeError?: string;
}>();

// Emit events back to the parent for two-way binding
const emit = defineEmits(['update:isPermanent', 'update:startDate', 'update:endDate', 'update:startTime', 'update:endTime']);

// Create reactive references using v-model-like bindings
const isPermanent = useVModel(props, 'isPermanent', emit);
const startDate = useVModel(props, 'startDate', emit);
const endDate = useVModel(props, 'endDate', emit);
const startTime = useVModel(props, 'startTime', emit);
const endTime = useVModel(props, 'endTime', emit);

const bottomRef = ref<HTMLElement | null>(null);

// Watch for changes to the "Permanent" checkbox
watch(isPermanent, async (newVal, oldVal) => {
    const current = now(getLocalTimeZone()).toDate();

    // Set default values based on current date/time
    const defaultStartDate = new CalendarDate(current.getFullYear(), current.getMonth() + 1, current.getDate());
    const defaultEndDate = new CalendarDate(current.getFullYear() + 1, current.getMonth() + 1, current.getDate());
    const defaultTime = new Time(current.getHours(), current.getMinutes());
    const formattedTime = formatTime(defaultTime);

    if (newVal) {
        // Clear all date/time fields when "Permanent" is enabled
        startDate.value = '';
        startTime.value = '';
        endDate.value = '';
        endTime.value = '';
    } else if (oldVal === true) {
        // Restore default values only if all fields were previously empty
        const allEmpty = !startDate.value && !startTime.value && !endDate.value && !endTime.value;

        if (allEmpty) {
            startDate.value = defaultStartDate.toString(); // "YYYY-MM-DD"
            endDate.value = defaultEndDate.toString(); // "YYYY-MM-DD"
            startTime.value = formattedTime; // "HH:mm"
            endTime.value = formattedTime;
        }
    }

    // Auto-scroll to the bottom after rendering
    if (!newVal) {
        await nextTick();
        bottomRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<template>
    <div class="flex flex-col gap-y-6">
        <InputCheckboxWithLabel label="form.label.permanent" v-model="isPermanent" />
        <div v-if="!isPermanent" class="flex flex-col gap-y-4">
            <InputDateAndTime
                label="form.label.start_access"
                :date="startDate"
                :time="startTime"
                @update:date="(value) => (startDate = value)"
                @update:time="(value) => (startTime = value)"
                :error="props.startDateError || props.startTimeError"
            />

            <InputDateAndTime
                label="form.label.end_access"
                :date="endDate"
                :time="endTime"
                @update:date="(value) => (endDate = value)"
                @update:time="(value) => (endTime = value)"
                :error="props.endDateError || props.endTimeError"
            />
            <div ref="bottomRef"></div>
        </div>
    </div>
</template>
