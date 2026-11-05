<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DateOption } from '@/types';

defineProps<{
    days: Array<DateOption>;
    modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <Select :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)">
        <SelectTrigger
            class="fluid-text-xs-sm focus-visible:ring-ring/20 data-[placeholder]:text-secondary-muted-text w-34 cursor-pointer shadow-none focus-visible:ring"
        >
            <SelectValue />
        </SelectTrigger>

        <SelectContent>
            <SelectItem v-for="day in days" :key="day.value" :value="day.value" class="fluid-text-xs-sm">
                {{ $t(`date_range_filter.${day.label}`) }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
