<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useVModel } from '@vueuse/core';

// Define props: label (optional) and modelValue for v-model support
const props = defineProps<{
    label?: string;
    modelValue?: boolean;
}>();

// Declare emit for v-model updates
const emit = defineEmits(['update:modelValue']);

// Create a two-way binding between parent and local `checked` state
const checked = useVModel(props, 'modelValue', emit);
</script>

<template>
    <!-- Wrapper container with spacing -->
    <div class="items-top flex gap-x-2">
        <!-- Bound checkbox using v-model -->
        <Checkbox id="checkbox" v-model="checked" />

        <!-- Label beside the checkbox -->
        <div class="flex items-center space-x-2">
            <Label for="checkbox">
                {{ $t(props.label) }}
            </Label>
        </div>
    </div>
</template>
