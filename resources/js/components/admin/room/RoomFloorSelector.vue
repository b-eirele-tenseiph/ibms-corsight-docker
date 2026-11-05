<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Combobox, ComboboxAnchor, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { Label } from '@/components/ui/label';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import type { Floor } from '@/types';
import { Icon } from '@iconify/vue';
import { computed, ref, watch } from 'vue';

// Props: floors to display, update mode flag, and optionally preselected floor name
const props = defineProps<{
    floors: Floor[];
    isUpdate?: boolean;
    selected?: string | null;
}>();

// Emits events to parent component
const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
    (e: 'selectFloor', payload: { name: string; id: number | null }): void;
    (e: 'deleteFloor', floor: Floor): void;
}>();

// Local state for combobox and tag input
const floorInput = ref<string[]>([]);
const comboboxOpen = ref(false);
const floorInputText = ref('');

// Normalizes a string for comparison (trim + lowercase)
const normalize = (val: string) => val.trim().toLowerCase();

// Filters floor options based on current input
const filteredOptions = computed(() => props.floors.filter((f) => normalize(f.name).includes(normalize(floorInputText.value))));

// Checks if the current input matches exactly an existing floor name
const hasExactMatch = computed(() => props.floors.some((f) => normalize(f.name) === normalize(floorInputText.value)));

// Combines filtered floors and new entry option (if no match)
const displayOptions = computed(() => {
    const options = [...filteredOptions.value];
    const trimmed = floorInputText.value.trim();

    if (trimmed && !hasExactMatch.value) {
        options.push({ name: trimmed, id: -1 });
    }

    return options;
});

// Extracts string from different value types
function extractValue(val: any): string | null {
    if (typeof val === 'string' || typeof val === 'number') return String(val);
    if (Array.isArray(val)) return val[0] ? String(val[0]) : null;
    if (val && typeof val === 'object' && 'value' in val) return String(val.value);
    return null;
}

// Clears the selected floor input
function clearSelection() {
    floorInput.value = [];
    floorInputText.value = '';
    emit('selectFloor', { name: '', id: null });
}

// Handles selection from dropdown or tag input
function handleFloorSelect(val: unknown) {
    const selected = extractValue(val);
    if (!selected) return;

    const match = props.floors.find((f) => f.name.toLowerCase() === selected.toLowerCase());

    floorInput.value = [selected];

    emit('selectFloor', {
        name: selected,
        id: match ? match.id : -1,
    });

    if (match || selected === floorInputText.value.trim()) {
        floorInputText.value = '';
    }
}

// Manually handles Enter key to create or select a floor
function handleManualEnter() {
    const input = floorInputText.value.trim();
    if (!input) return;

    // Same logic as before
    const match = props.floors.find((f) => normalize(f.name) === normalize(input));
    const selected = match ? match.name : input;

    handleFloorSelect(selected);
}
// Generates a unique key for each floor option
function getFloorKey(floor: Floor): string {
    const idPart = floor.id !== -1 ? floor.id : 'new';
    const namePart = floor.name?.trim() || 'unnamed';
    return `${idPart}-${namePart}`;
}

// Emits event to parent to request deletion of a floor
function requestDelete(floor: Floor) {
    emit('deleteFloor', floor);
}

// Watches for changes to the `selected` prop and syncs input
watch(
    () => props.selected,
    (newVal) => {
        if (newVal) {
            floorInput.value = [newVal];
        } else {
            floorInput.value = [];
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="grid w-full gap-1.5">
        <Label for="floor" class="fluid-text-xs-sm">{{ $t('form.label.floor_name') }}</Label>
        <Combobox
            :model-value="floorInput"
            v-model:open="comboboxOpen"
            @update:inputValue="(val) => (floorInputText.value = val)"
            @update:modelValue="handleFloorSelect"
            class="focus:outline-ring rounded-md focus:outline"
        >
            <ComboboxAnchor as-child>
                <TagsInput
                    :model-value="floorInput"
                    class="dark:bg-input/30 focus-within:ring-ring border-input bg-background hover:bg-muted w-full gap-2 border px-1 py-1 transition-colors focus-within:ring"
                    :max="1"
                >
                    <div class="w-full pl-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <TagsInputItem v-if="floorInput[0]" :value="floorInput[0]" :class="getDefaultBadgeClass('0')">
                                <TagsInputItemText />
                                <TagsInputItemDelete @click="clearSelection" />
                            </TagsInputItem>
                        </div>
                        <div class="w-full">
                            <ComboboxInput v-if="!floorInput[0]" v-model="floorInputText" @keydown.enter.prevent="handleManualEnter" as-child>
                                <TagsInputInput
                                    id="floor"
                                    :placeholder="$t('form.placeholder.floor_name')"
                                    class="placeholder:text-secondary-muted-text text-primary-text fluid-text-xs-sm h-auto w-full border-none p-0 focus-visible:ring-0"
                                />
                                <ComboboxTrigger>
                                    <Icon icon="lucide:chevron-down" class="size-4" />
                                </ComboboxTrigger>
                            </ComboboxInput>
                        </div>
                    </div>
                </TagsInput>

                <ComboboxList class="fluid-combobox-width" v-if="comboboxOpen">
                    <ComboboxGroup class="max-h-[300px] min-h-[25px] w-full overflow-y-scroll p-2">
                        <ComboboxItem
                            v-for="option in displayOptions"
                            :key="getFloorKey(option)"
                            :value="option.name"
                            class="text-primary-text flex w-full items-center justify-between truncate p-1"
                        >
                            <span class="fluid-text-xxs-sm tag-style">{{ option.name }}</span>
                            <span v-if="option.id === -1">(new)</span>
                            <Button v-if="props.isUpdate && option.id !== -1" variant="ghost" class="ml-2 p-0" @click.stop="requestDelete(option)">
                                <Icon icon="fa6-regular:trash-can" class="size-4" />
                            </Button>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxList>
            </ComboboxAnchor>
        </Combobox>
    </div>
</template>
