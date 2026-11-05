<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Icon } from '@iconify/vue';
import { CalendarDate, parseDate } from '@internationalized/date';
import { useVModel } from '@vueuse/core';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DatePicker, NonPastDatePicker } from '../date-picker';

// Define prop to accept bound date string
const props = withDefaults(
    defineProps<{
        modelValue: string;
        allowPastDate?: boolean;
    }>(),
    {
        allowPastDate: false,
    },
);

// Set up emit for two-way binding
const emit = defineEmits(['update:modelValue']);
const model = useVModel(props, 'modelValue', emit);

// Reactive state for controlling popover visibility
const isOpen = ref(false);

// Computed binding: converts string to CalendarDate and back
const selectedDate = computed<CalendarDate | undefined>({
    get() {
        return model.value ? parseDate(model.value) : undefined;
    },
    set(value) {
        model.value = value ? value.toString() : '';
    },
});

// Computed text for display
const formattedDate = computed(() => {
    return model.value || useI18n().t('form.placeholder.date');
});
</script>

<template>
    <!-- Popover container for the date picker -->
    <Popover v-model:open="isOpen">
        <!-- Trigger button for opening the calendar popover -->
        <PopoverTrigger as-child>
            <Button variant="outline" class="h-full w-full justify-start text-left font-normal">
                <Icon icon="lucide:calendar" />
                <p>{{ formattedDate }}</p>
            </Button>
        </PopoverTrigger>

        <!-- Calendar popover content -->
        <PopoverContent class="w-auto p-0">
            <!--
                Conditional rendering of DatePicker and NonPastDatePicker
                based on allowPastDate boolean value
            -->
            <component :is="props.allowPastDate ? DatePicker : NonPastDatePicker" v-model="selectedDate" />
        </PopoverContent>
    </Popover>
</template>
