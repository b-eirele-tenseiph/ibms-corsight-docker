<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { DateFormatter, getLocalTimeZone } from '@internationalized/date';
import type { DateRange } from 'reka-ui';
import { computed, ref } from 'vue';

const props = defineProps<{ modelValue: DateRange }>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: DateRange): void;
}>();

// Computed binding for v-model
const value = computed({
    get: () => props.modelValue,
    set: (val: DateRange) => emit('update:modelValue', val),
});

// Formatter for displaying selected date range
const df = new DateFormatter('en-US', {
    dateStyle: 'medium',
});

const popoverOpen = ref(false);

function resetCalendar() {
    value.value = { start: undefined, end: undefined };
    popoverOpen.value = false;
}
</script>

<template>
    <Popover v-model:open="popoverOpen">
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('w-full justify-start bg-transparent text-left font-normal', !value && 'text-muted-foreground')">
                <Icon icon="lucide:calendar" class="hidden size-3 sm:size-4 lg:inline" />
                <template v-if="value.start">
                    <template v-if="value.end">
                        <span class="fluid-text-xxs-xs">
                            {{ df.format(value.start.toDate(getLocalTimeZone())) }} -
                            {{ df.format(value.end.toDate(getLocalTimeZone())) }}
                        </span>
                    </template>
                    <template v-else>
                        {{ df.format(value.start.toDate(getLocalTimeZone())) }}
                    </template>
                </template>
                <template v-else>
                    <span class="fluid-text-xs-sm"> {{ $t('form.placeholder.date') }} </span>
                </template>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="mr-8 w-auto p-0 sm:mr-0">
            <RangeCalendar v-model="value" initial-focus :number-of-months="2" @confirm="popoverOpen = false" @cancel="resetCalendar()" />
        </PopoverContent>
    </Popover>
</template>
