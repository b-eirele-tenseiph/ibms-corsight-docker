<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { computed, ref, type HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    items: any[];
    modelValue?: any[];
    placeholder?: string;
    valueKey?: string;
    labelKey?: string;
    class?: HTMLAttributes['class'];
    emptyLabel?: string;
    maxBadgeCount?: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: any[]): void;
}>();

const { t, te } = useI18n();

const open = ref(false);

const maxBadgeCount = computed(() => props.maxBadgeCount ?? 2);

const valueKey = computed(() => props.valueKey ?? 'id');

const labelKey = computed(() => props.labelKey ?? 'name');

const selectedValues = computed(() => props.modelValue ?? []);

const selectedItems = computed(() => props.items.filter((item) => selectedValues.value.includes(item[valueKey.value])));

const displayLabel = computed(() => {
    if (selectedItems.value.length) return t('filter.selected_count', { count: selectedItems.value.length });

    if (props.placeholder) return te(props.placeholder) ? t(props.placeholder) : props.placeholder;

    return t('filter.select_item');
});

const isSelected = (item: any) => selectedValues.value.includes(item[valueKey.value]);

function toggleSelect(item: any) {
    const val = item[valueKey.value];
    const next = isSelected(item) ? selectedValues.value.filter((v) => v !== val) : [...selectedValues.value, val];

    emit('update:modelValue', next);
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <button
                type="button"
                role="combobox"
                :aria-expanded="open"
                class="border-input bg-background hover:bg-accent/30 focus-visible:border-ring focus-visible:ring-ring/50 inline-flex h-auto min-h-9 max-w-full min-w-[120px] cursor-pointer items-center justify-between rounded-md border px-2 py-1 text-left text-sm outline-none focus-visible:ring-[3px]"
                :class="props.class"
            >
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1">
                    <template v-if="selectedItems.length">
                        <Badge
                            v-for="item in selectedItems.slice(0, maxBadgeCount)"
                            :key="item[valueKey]"
                            :class="cn(getDefaultBadgeClass(item[labelKey]), 'max-w-full text-left break-words whitespace-normal capitalize')"
                        >
                            <span class="line-clamp-1 truncate sm:whitespace-normal">{{ item[labelKey] }}</span>
                        </Badge>
                        <Badge v-if="selectedItems.length > maxBadgeCount" class="bg-secondary-text/10 text-secondary-text">
                            +{{ selectedItems.length - maxBadgeCount }} selected
                        </Badge>
                    </template>

                    <span v-else class="fluid-text-xs-sm text-secondary-muted-text truncate">
                        {{ displayLabel }}
                    </span>
                </div>

                <Icon icon="lucide:chevrons-up-down" class="text-muted-foreground text-secondary-muted-text ml-2 size-4 shrink-0 opacity-70" />
            </button>
        </PopoverTrigger>

        <PopoverContent
            class="-mt-10 w-[var(--reka-popper-anchor-width)] p-0 shadow-none data-[state=closed]:animate-none data-[state=open]:animate-none"
        >
            <Command class="w-full">
                <CommandInput :placeholder="$t('operation.search')" class="h-9" />

                <CommandList>
                    <CommandEmpty>
                        {{ $t(emptyLabel ?? 'form.label.empty_selection') }}
                    </CommandEmpty>

                    <CommandGroup v-if="props.items.length > 0">
                        <CommandItem
                            v-for="item in props.items"
                            :key="item[valueKey]"
                            :value="String(item[labelKey] ?? '')"
                            @select="() => toggleSelect(item)"
                            :class="
                                cn('flex cursor-pointer items-center gap-2 rounded-sm select-none', 'hover:bg-accent hover:text-accent-foreground')
                            "
                        >
                            <Icon :icon="isSelected(item) ? 'lucide:check-square' : 'lucide:square'" class="size-4 opacity-80" />
                            <slot name="item" :item="item" :selected="isSelected(item)">
                                <span class="fluid-text-xs-sm capitalize">
                                    {{ item[labelKey] }}
                                </span>
                            </slot>
                        </CommandItem>
                    </CommandGroup>

                    <CommandGroup v-else>
                        <div class="py-5 text-center text-sm">
                            {{ $t(emptyLabel ?? 'form.label.empty_selection') }}
                        </div>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
