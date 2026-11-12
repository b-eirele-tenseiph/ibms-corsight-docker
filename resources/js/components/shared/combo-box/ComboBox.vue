<script setup lang="ts">
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { computed, ref, type HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    items: any[];
    modelValue?: any;
    placeholder?: string;
    valueKey?: string;
    labelKey?: string;
    class?: HTMLAttributes['class'];
    emptyLabel?: string;
    returnObject?: boolean;
    disabled?: boolean;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
}>();

const { t, te } = useI18n();

const open = ref(false);

// Resolved keys — avoids repeated nullish coalescing in every computed
const vKey = computed(() => props.valueKey ?? 'id');
const lKey = computed(() => props.labelKey ?? 'name');

const selectedItem = computed(() => {
    const comparand = props.returnObject ? props.modelValue?.[vKey.value] : props.modelValue;
    return props.items.find((item) => item[vKey.value] === comparand);
});

const displayLabel = computed(() => {
    if (selectedItem.value) return selectedItem.value[lKey.value] ?? '';
    if (props.placeholder) return te(props.placeholder) ? t(props.placeholder) : props.placeholder;
    return t('filter.select_item');
});

function handleSelect(item: any) {
    if (props.disabled) return;
    emit('update:modelValue', props.returnObject ? item : item[vKey.value]);
    open.value = false;
}

function isSelected(item: any) {
    return item[vKey.value] === selectedItem.value?.[vKey.value];
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <button
                type="button"
                role="combobox"
                :aria-expanded="open"
                :disabled="props.disabled"
                :class="
                    cn(
                        'border-input bg-background hover:bg-accent/30 disabled:bg-accent/70 focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full items-center justify-between rounded-md border px-3 text-left text-sm outline-none focus-visible:ring-[3px]',
                        props.disabled && 'cursor-not-allowed opacity-75',
                        props.error && 'border-red-600',
                        props.class,
                    )
                "
            >
                <span
                    class="fluid-text-xs-sm w-full truncate pr-2 text-start capitalize"
                    :class="selectedItem ? 'text-primary-text' : 'text-secondary-text'"
                >
                    {{ displayLabel }}
                </span>
                <Icon icon="lucide:chevrons-up-down" class="text-secondary-text size-3.5 shrink-0 opacity-60" />
            </button>
        </PopoverTrigger>

        <PopoverContent align="start" class="w-(--reka-popper-anchor-width) p-0 shadow-md">
            <Command class="w-full">
                <CommandInput :placeholder="$t('operation.search')" class="fluid-text-xs-sm h-9 border-b" />
                <CommandList class="max-h-60">
                    <CommandEmpty class="text-secondary-muted-text fluid-text-xs-sm py-4 text-center text-sm">
                        {{ $t(emptyLabel ?? 'form.label.empty_selection') }}
                    </CommandEmpty>

                    <CommandGroup class="flex flex-col gap-y-0.5 p-1">
                        <CommandItem
                            v-for="item in props.items"
                            :key="item[vKey]"
                            :value="String(item[lKey] ?? '')"
                            :class="
                                cn(
                                    'fluid-text-xs-sm hover:bg-accent hover:text-accent-foreground flex cursor-pointer items-center justify-between capitalize transition-colors duration-100',
                                    isSelected(item) ? 'bg-accent/60 text-accent-foreground' : 'text-foreground',
                                )
                            "
                            @select="() => handleSelect(item)"
                        >
                            <slot name="item" :item="item" :selected="isSelected(item)">
                                <span class="truncate">{{ item[lKey] }}</span>
                            </slot>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
