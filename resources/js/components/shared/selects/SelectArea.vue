<script lang="ts" setup>
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { useForwardPropsEmits } from 'reka-ui';
import { HTMLAttributes, useAttrs } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    items: any[];
    modelValue?: any;
    placeholder?: string;
    defaultValue?: string;
    valueKey?: string;
    itemKey?: string;
    emptyLabel?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const forwarded = useForwardPropsEmits(props, emit);
</script>

<template>
    <Select v-bind="{ ...forwarded, ...useAttrs() }">
        <SelectTrigger
            :class="
                cn(
                    'fluid-text-xs-sm focus-visible:ring-ring/20 data-placeholder:text-secondary-muted-text w-full cursor-pointer capitalize shadow-none focus-visible:ring',
                    props.class,
                )
            "
        >
            <SelectValue class="h-9" :placeholder="$te(placeholder) ? $t(placeholder) : (placeholder ?? $t('filter.select_item'))" />
        </SelectTrigger>
        <SelectContent>
            <ScrollArea v-if="items.length" class="max-h-64">
                <SelectItem class="fluid-text-xs-sm" v-if="defaultValue" :value="null"> {{ $t(defaultValue) }} </SelectItem>
                <SelectItem class="fluid-text-xs-sm capitalize" v-for="item in items" :key="item[valueKey ?? 'id']" :value="item[valueKey ?? 'id']">
                    {{ item[itemKey ?? 'name'] }}
                </SelectItem>
            </ScrollArea>

            <template v-else>
                <div class="text-muted-foreground text-secondary-muted-text fluid-text-xs-sm px-2 py-1 shadow-none select-none">
                    {{ $t(emptyLabel ?? 'form.label.empty_selection') }}
                </div>
            </template>
        </SelectContent>
    </Select>
</template>
