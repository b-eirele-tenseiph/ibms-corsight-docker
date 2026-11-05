<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import { HTMLAttributes } from 'vue';
import { toast } from 'vue-sonner';

// Handle two-way binding
const modelValue = defineModel<string | number>({ required: true });

const props = withDefaults(
    defineProps<{
        label: string;
        placeholder?: string;
        error?: string;
        readonly?: boolean;
        type?: string;
        icon?: string;
        disabled?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        placeholder: '',
        readonly: false,
        type: 'text',
        icon: 'lucide:refresh-cw',
        disabled: false,
    },
);

const emit = defineEmits(['click']);

const handleInputClick = async () => {
    if (modelValue.value) {
        await navigator.clipboard.writeText(modelValue.value.toString());
        toast.info('Reset Link Copied Successfully');
    }
};
</script>

<template>
    <div :class="['grid w-full space-y-2', props.class]">
        <Label v-if="label" class="fluid-text-xs-sm">{{ $t(label) }}</Label>

        <div class="flex items-center gap-2">
            <div class="relative w-full">
                <Input
                    v-model="modelValue"
                    :type="type"
                    :placeholder="placeholder ? $t(placeholder) : ''"
                    :readonly="readonly"
                    class="fluid-text-xs-sm focus-visible:border-ring focus-visible:ring-ring/20 placeholder:text-secondary-muted-text cursor-pointer shadow-none select-none focus-visible:ring"
                    @click="handleInputClick"
                />
            </div>

            <Button type="button" variant="outline" size="icon" :disabled="disabled" @click.prevent="emit('click')" class="shrink-0">
                <Icon :icon="icon" class="h-4 w-4" />
            </Button>
        </div>

        <div v-show="error">
            <p class="text-destructive-text text-sm">{{ error }}</p>
        </div>
    </div>
</template>
