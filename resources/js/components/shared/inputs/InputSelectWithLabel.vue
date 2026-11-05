<script lang="ts" setup>
import { SelectArea } from '@/components/shared/selects';
import { Label } from '@/components/ui/label';
import { useAttrs } from 'vue';

defineProps<{
    items: any[];
    label?: string;
    placeholder?: string;
    error?: string;
}>();

const model = defineModel('modelValue');
const attrs = useAttrs();
</script>

<template>
    <div class="grid w-full space-y-2">
        <Label v-if="label" class="fluid-text-xs-sm"> {{ $te(label) ? $t(label) : label }}</Label>

        <SelectArea v-bind="attrs" :items="items" v-model="model" :placeholder="placeholder" />

        <p v-show="error" class="text-destructive fluid-text-xs-sm">{{ error }}</p>
    </div>
</template>
