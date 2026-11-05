<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import { useForwardPropsEmits } from 'reka-ui';
import { computed, HTMLAttributes, ref, useAttrs } from 'vue';

// Prevent automatic attribute inheritance
defineOptions({ inheritAttrs: false });

// Define component props with defaults
const props = withDefaults(
    defineProps<{
        label?: string;
        type?: string;
        placeholder?: string;
        modelValue?: string | number;
        error?: string;
        class?: HTMLAttributes['class'];
    }>(),
    { type: 'text' },
);

// Emit model updates to parent
const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

// Toggles password visibility when input type is password
const showPassword = ref(false);

// Forwarded props and emits
const forwarded = useForwardPropsEmits(props, emit);

// Tracks input focus to conditionally show the toggle icon
const isFocused = ref(false);

const inputType = computed(() => (props.type === 'password' ? (showPassword.value ? 'text' : 'password') : props.type));
</script>
<template>
    <div class="grid w-full space-y-2">
        <Label v-if="label" class="fluid-text-xs-sm">{{ $t(label) }}</Label>
        <div class="relative">
            <Input
                v-bind="{ ...forwarded, ...useAttrs() }"
                :type="inputType"
                :placeholder="placeholder ? $t(placeholder) : ''"
                class="fluid-text-xs-sm focus-visible:border-ring focus-visible:ring-ring/20 placeholder:text-secondary-muted-text relative shadow-none focus-visible:ring"
                @focus="isFocused = true"
                @blur="isFocused = false"
            />
            <div
                v-if="type === 'password' && isFocused"
                @mousedown.prevent="showPassword = !showPassword"
                class="text-secondary-text absolute top-2.5 right-2 transform cursor-pointer text-lg"
                tabindex="-1"
            >
                <Icon :icon="!showPassword ? 'lucide:eye-off' : 'lucide:eye'" />
            </div>
        </div>

        <!-- Error message display -->
        <div v-show="error">
            <p class="text-destructive fluid-text-xs-sm">{{ error }}</p>
        </div>
    </div>
</template>
