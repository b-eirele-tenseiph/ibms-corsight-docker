<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Icon } from '@iconify/vue';
import { ref, watch } from 'vue';

const props = defineProps<{
    modelValue: File | null;
    photo_file_path?: string | null;
    error?: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
    (e: 'update:error', value: string | null): void;
}>();

const preview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const generatePreview = (file: File | string | null) => {
    if (!file) return null;
    if (file instanceof File) {
        return URL.createObjectURL(file);
    }
    if (typeof file === 'string') {
        return file.startsWith('http') ? file : `/storage/${file}`;
    }
    return null;
};

// Watch modelValue changes to refresh preview
watch(
    () => [props.modelValue, props.photo_file_path],
    ([file, existing]) => {
        if (file) {
            preview.value = generatePreview(file);
        } else if (existing) {
            preview.value = generatePreview(existing);
        } else {
            preview.value = null;
        }
    },
    { immediate: true },
);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const selectedFile = target.files?.[0] || null;
    if (!selectedFile) return;

    preview.value = generatePreview(selectedFile);
    emit('update:modelValue', selectedFile);
    emit('update:error', null);

    target.value = ''; // reset input
};

const openFilePicker = () => {
    fileInput.value?.click();
};
</script>
<template>
    <div class="flex h-50 w-50 items-start justify-start gap-x-2.5">
        <div class="relative flex flex-wrap gap-2.5">
            <!-- Preview -->
            <div v-if="preview" class="group relative h-full w-full cursor-pointer overflow-hidden rounded border" @click="openFilePicker">
                <img :src="preview" alt="Preview" class="h-50 w-50 object-cover object-top" />
            </div>

            <!-- Upload button -->
            <div
                v-else
                @click="openFilePicker"
                class="border-button-border flex h-50 w-50 cursor-pointer items-center justify-center rounded-md border-3 border-dashed p-4 text-center text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950"
            >
                <Icon icon="lucide:image" class="text-sidebar-border h-[50px] w-[50px]" />
            </div>

            <!-- 🔔 Tooltip (works for both preview + empty input) -->
            <TooltipProvider>
                <Tooltip v-if="props.error" :open="!!props.error">
                    <TooltipTrigger as-child>
                        <Button
                            variant="ghost"
                            class="absolute bottom-0.5 left-0.5 h-7 w-7 rounded-full bg-white/80 p-0 dark:bg-black/50"
                            @click.stop
                        >
                            <Icon icon="lucide:triangle-alert" class="text-destructive h-4 w-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent
                        class="dark:bg-destructive max-w-[200px] break-words whitespace-normal md:max-w-[500px]"
                        tooltip-arrow-style="dark:bg-destructive dark:fill-destructive"
                    >
                        <p class="text-destructive fluid-text-xs-sm text-center dark:text-red-100">
                            {{ props.error }}
                        </p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <input ref="fileInput" type="file" accept="image/*" @change="handleFileChange" class="hidden" />
        </div>
    </div>
</template>
