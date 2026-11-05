<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { getDefaultBadgeClass } from '@/lib/badge-colors';
import { RoomArea } from '@/types';
import { AcceptableInputValue } from 'reka-ui';
import { computed } from 'vue';

const modelValue = defineModel<RoomArea[]>({ default: [] });

const tagValues = computed(() => modelValue.value.map((a) => a.name));

function addTag(value: AcceptableInputValue) {
    const name = value.toString().trim();
    if (name && !modelValue.value.some((a) => a.name === name)) modelValue.value = [...modelValue.value, { id: null, name }];
}

function removeTag(value: AcceptableInputValue) {
    modelValue.value = modelValue.value.filter((a) => a.name !== value);
}
</script>

<template>
    <div class="grid h-auto w-full items-center gap-1.5">
        <Label class="fluid-text-xs-sm">{{ $t('form.label.room_area') }}</Label>

        <TagsInput
            :model-value="tagValues"
            class="dark:bg-input/30 focus-within:ring-ring border-input bg-background hover:bg-muted focus:outline-ring h-auto min-h-9 w-full gap-2 rounded-md border px-1 py-1 transition-colors focus-within:ring focus:outline focus-visible:outline-0"
            @add-tag="addTag"
            @remove-tag="removeTag"
        >
            <div class="flex w-full flex-wrap items-center gap-2 px-2">
                <TagsInputItem
                    v-for="(item, index) in modelValue"
                    :key="item.id ?? index"
                    :value="item.name"
                    :class="getDefaultBadgeClass(item.name)"
                >
                    <TagsInputItemText />
                    <TagsInputItemDelete />
                </TagsInputItem>

                <TagsInputInput id="room-area" :placeholder="$t('form.placeholder.room_area')" />
            </div>
        </TagsInput>
    </div>
</template>
