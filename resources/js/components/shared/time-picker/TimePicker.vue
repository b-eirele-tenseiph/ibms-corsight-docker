<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Time } from '@internationalized/date';
import { useVModel } from '@vueuse/core';

// Define props and emit for two-way binding of Time value
const props = defineProps<{
    modelValue?: Time;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Time): void;
}>();

// Reactive binding for the selected time
const time = useVModel(props, 'modelValue', emit);

// Generate hours (1–12) in reverse for layout
const hours = Array.from({ length: 12 }, (_, i) => i + 1).reverse();

// Generate minutes (0–59)
const minutes = Array.from({ length: 60 }, (_, i) => i);

// AM/PM options
const ampmOptions = ['AM', 'PM'];

// Handles changes to hour, minute, or AM/PM parts of the time.
// Updates `time` accordingly.
function handleTimeChange(type: 'hour' | 'minute' | 'ampm', value: string) {
    const current = time.value ?? new Time(0, 0);

    if (type === 'hour') {
        const isPM = current.hour >= 12;
        const hour = (parseInt(value) % 12) + (isPM ? 12 : 0);
        time.value = new Time(hour, current.minute);
    }

    if (type === 'minute') {
        time.value = new Time(current.hour, parseInt(value));
    }

    if (type === 'ampm') {
        const hour = current.hour;
        if (value === 'AM' && hour >= 12) {
            time.value = new Time(hour - 12, current.minute);
        } else if (value === 'PM' && hour < 12) {
            time.value = new Time(hour + 12, current.minute);
        }
    }
}
</script>

<template>
    <div class="flex flex-col divide-y sm:h-[300px] sm:flex-row sm:divide-x sm:divide-y-0">
        <!-- Hour Picker -->
        <ScrollArea class="w-64 sm:w-auto">
            <div class="flex p-2 sm:flex-col">
                <Button
                    v-for="hour in hours"
                    :key="hour"
                    size="icon"
                    :variant="(time?.hour ?? 0) % 12 === hour % 12 ? 'default' : 'ghost'"
                    class="aspect-square shrink-0 sm:w-full"
                    @click="handleTimeChange('hour', hour.toString())"
                >
                    {{ hour }}
                </Button>
            </div>
            <ScrollBar orientation="horizontal" />
        </ScrollArea>

        <!-- Minute Picker -->
        <ScrollArea class="w-64 sm:w-auto">
            <div class="flex p-2 sm:flex-col">
                <Button
                    v-for="minute in minutes"
                    :key="minute"
                    size="icon"
                    :variant="(time?.minute ?? 0) === minute ? 'default' : 'ghost'"
                    class="aspect-square shrink-0 sm:w-full"
                    @click="handleTimeChange('minute', minute.toString())"
                >
                    {{ minute.toString().padStart(2, '0') }}
                </Button>
            </div>
            <ScrollBar orientation="horizontal" class="sm:hidden" />
        </ScrollArea>

        <!-- AM/PM Picker -->
        <ScrollArea>
            <div class="flex p-2 sm:flex-col">
                <Button
                    v-for="option in ampmOptions"
                    :key="option"
                    size="icon"
                    :variant="(option === 'AM' && (time?.hour ?? 0) < 12) || (option === 'PM' && (time?.hour ?? 0) >= 12) ? 'default' : 'ghost'"
                    class="aspect-square shrink-0 sm:w-full"
                    @click="handleTimeChange('ampm', option)"
                >
                    {{ option }}
                </Button>
            </div>
        </ScrollArea>
    </div>
</template>
