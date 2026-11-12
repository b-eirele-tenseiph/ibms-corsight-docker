<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { computed, useAttrs } from 'vue';
import { useI18n } from 'vue-i18n';
import { ComboBoxMultiSelect } from '../combo-box';

const props = defineProps<{
    items: any[];
    label?: string;
    placeholder?: string;
    error?: string;
    valueKey?: string;
    itemKey?: string;
    emptyLabel?: string;
    maxBadgeCount?: number;
}>();

const model = defineModel<any[]>('modelValue', { default: () => [] });
const attrs = useAttrs();
const { t, te } = useI18n();

const labelText = computed(() => {
    if (!props.label || !props.label.trim()) return null;
    return te(props.label) ? t(props.label) : props.label;
});
</script>

<template>
    <div class="grid w-full space-y-2">
        <Label v-if="labelText" class="fluid-text-xs-sm">{{ labelText }}</Label>

        <ComboBoxMultiSelect
            v-bind="attrs"
            v-model="model"
            :items="items"
            :placeholder="placeholder"
            :value-key="valueKey"
            :label-key="itemKey"
            :empty-label="emptyLabel"
            :max-badge-count="maxBadgeCount"
        >
            <template v-if="$slots.item" #item="slotProps">
                <slot name="item" v-bind="slotProps" />
            </template>
        </ComboBoxMultiSelect>

        <!-- Error message display -->
        <p v-show="error" class="text-destructive fluid-text-xs-sm font-medium">{{ error }}</p>
    </div>
</template>
