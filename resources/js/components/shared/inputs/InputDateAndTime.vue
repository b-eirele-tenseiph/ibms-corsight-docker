<script setup lang="ts">
import { InputDatePicker, InputTimePicker } from '@/components/shared/inputs';
import { Label } from '@/components/ui/label';
import { useVModel } from '@vueuse/core';

// Define incoming props: label for display, error message, date & time values
const props = defineProps<{
    label?: string;
    error?: string;
    date: string;
    time: string;
}>();

// Define emitted events for updating date and time
const emit = defineEmits<{
    (e: 'update:date', value: string): void;
    (e: 'update:time', value: string): void;
}>();

// Bind props.date and props.time with reactivity using useVModel
const date = useVModel(props, 'date', emit);
const time = useVModel(props, 'time', emit);
</script>

<template>
    <div class="flex flex-col space-y-2">
        <!-- Display the field label if provided -->
        <div>
            <Label class="fluid-text-xs-sm">{{ $t(props.label) }}</Label>
        </div>

        <!-- Date and time inputs displayed in row (or column on small screens) -->
        <div class="xs:flex-row xs:gap-x-4 flex flex-col gap-y-2">
            <div class="flex-1">
                <!-- Date picker with two-way binding -->
                <InputDatePicker v-model="date" />
            </div>

            <div class="flex-1">
                <!-- Time picker with two-way binding -->
                <InputTimePicker v-model="time" />
            </div>
        </div>

        <!-- Show error message if any validation error is passed -->
        <div v-if="error">
            <p class="text-destructive text-sm">
                {{ props.error }}
            </p>
        </div>
    </div>
</template>
