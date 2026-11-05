<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Floor } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    floors: Array<Floor>;
    modelValue: number | null;
    error?: string;
    placeholder?: string;
    label?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const stringModel = computed({
    get: () => props.modelValue?.toString(),
    set: (val: string) => {
        emit('update:modelValue', val ? Number(val) : null);
    },
});
</script>

<template>
    <div class="grid gap-1.5">
        <label class="fluid-text-xs-sm font-medium">{{ $t(label) }}</label>
        <Select v-model="stringModel">
            <SelectTrigger class="data-[placeholder]:text-secondary-muted-text mb-2 w-full shadow-none hover:cursor-pointer">
                <SelectValue :placeholder="$t(placeholder)" />
            </SelectTrigger>
            <SelectContent class="max-h-64">
                <SelectItem class="fluid-text-xs-sm" v-for="floor in floors" :key="floor.id" :value="floor.id.toString()">
                    {{ floor.name }}
                </SelectItem>
            </SelectContent>
        </Select>
        <div v-if="error" class="text-sm text-red-500">
            {{ error }}
        </div>
    </div>
</template>
