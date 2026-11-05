<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Icon } from '@iconify/vue';
import { useForwardPropsEmits } from 'reka-ui';
import { useAttrs } from 'vue';

const props = defineProps<{
    modelValue?: string | number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const forwarded = useForwardPropsEmits(props, emit);
</script>

<template>
    <div class="xs:max-w-60 relative w-full max-w-50 items-center">
        <span class="absolute inset-y-0 end-0 flex items-center justify-center px-2">
            <Icon icon="lucide:search" class="size-4 opacity-50" />
        </span>

        <!-- Text input bound to inputValue -->
        <Input
            type="text"
            v-bind="{ ...forwarded, ...useAttrs() }"
            :placeholder="$t('operation.search')"
            class="fluid-text-xs-sm focus-visible:ring-ring/20 pr-8 shadow-none focus-visible:ring"
        />
    </div>
</template>
