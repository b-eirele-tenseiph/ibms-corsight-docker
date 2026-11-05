<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForwardPropsEmits } from 'reka-ui';
import { useAttrs } from 'vue';

const props = defineProps<{
    label: string;
    placeholder?: string;
    modelValue?: string | number;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const forwarded = useForwardPropsEmits(props, emit);
</script>

<template>
    <div class="grid w-full space-y-2">
        <Label class="fluid-text-xs-sm">{{ $t(label) }}</Label>
        <Textarea
            v-bind="{ ...forwarded, ...useAttrs() }"
            :placeholder="$t(placeholder)"
            class="focus-visible:border-ring fluid-text-xs-sm focus-visible:ring-ring/20 placeholder:text-secondary-muted-text shadow-none focus-visible:ring"
        />
        <p v-show="error" class="text-destructive fluid-text-xs-sm">{{ error }}</p>
    </div>
</template>
