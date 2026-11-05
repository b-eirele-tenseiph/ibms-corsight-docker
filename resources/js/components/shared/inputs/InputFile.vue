<script setup lang="ts">
import { Input } from '@/components/ui/input';

defineProps<{ modelValue?: File | null; error?: any }>();

const emit = defineEmits(['update:modelValue']);

const onChange = (e: Event) => {
    const el = e.target as HTMLInputElement;
    emit('update:modelValue', el.files?.[0] ?? null);
};
</script>

<template>
    <div class="flex flex-col gap-y-4.5">
        <Input
            type="file"
            name="file_excel"
            @click="($event.target as HTMLInputElement).value = ''"
            @change="onChange"
            class="file:bg-button-border dark:file:bg-secondary file:text-primary-text file:fluid-text-xs-sm fluid-text-xs-sm cursor-pointer py-0 pl-0 font-semibold file:mr-3 file:h-full file:cursor-pointer file:px-3 file:py-2 file:font-semibold"
        />

        <div v-show="error" class="bg-button-border dark:bg-background-overlay h-auto max-h-75 overflow-y-auto rounded-sm p-2">
            <p class="fluid-text-xs-sm text-error font-bold">{{ $t('error.unable_to_import') }}</p>
            <ul class="list-disc pl-8">
                <li class="fluid-text-xs-sm font-normal">{{ error }}</li>
            </ul>
        </div>
    </div>
</template>
