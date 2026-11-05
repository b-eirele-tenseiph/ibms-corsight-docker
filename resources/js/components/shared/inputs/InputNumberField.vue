<script setup lang="ts">
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';

const props = defineProps<{
    modelValue?: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
    (e: 'increment'): void;
    (e: 'decrement'): void;
}>();
</script>

<template>
    <NumberField :model-value="props.modelValue" :min="0" :max="100" @update:model-value="(val) => emit('update:modelValue', val)">
        <NumberFieldContent>
            <NumberFieldDecrement
                class="bg-controls text-secondary-text flex h-6 w-6 cursor-pointer items-center justify-center rounded-full p-0"
                @click="emit('decrement')"
            />
            <NumberFieldInput
                class="fluid-text-xxs-sm flex w-18 border-none p-0 text-sm font-semibold shadow-none focus:ring-0 focus:outline-none focus-visible:ring-0 sm:w-20"
            />

            <NumberFieldIncrement
                class="bg-primary flex h-6 w-6 cursor-pointer items-center justify-center rounded-full p-0 text-white"
                @click="emit('increment')"
            />
        </NumberFieldContent>
    </NumberField>
</template>
