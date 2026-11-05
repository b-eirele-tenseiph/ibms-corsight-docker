<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import { ref, watch } from 'vue';

// Generic type for existing photos/items
type PhotoLike = { [key: string]: any };

// Props
const props = defineProps<{
    modelValue: (File | PhotoLike)[];
    error?: string;
    label: string;
    placeholder?: string;
    getPreviewUrl: (item: PhotoLike) => string;
    maxItems?: number;
    hint?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: (File | PhotoLike)[]): void;
}>();

const items = ref<(File | PhotoLike)[]>(props.modelValue || []);
const previews = ref<string[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);

const generatePreviews = (list: (File | PhotoLike)[]) => {
    previews.value = list.map((item) => {
        if (item instanceof File) {
            return URL.createObjectURL(item);
        }
        return props.getPreviewUrl(item);
    });
};

watch(
    () => props.modelValue,
    (newVal) => {
        items.value = newVal || [];
        generatePreviews(items.value);
    },
    { immediate: true },
);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const selectedFiles = target.files;
    if (!selectedFiles) return;

    const newFiles = Array.from(selectedFiles);
    items.value.push(...newFiles);
    generatePreviews(items.value);

    emit('update:modelValue', items.value);

    // Reset input so same file can be reselected
    target.value = '';
};

const removeImage = (index: number) => {
    items.value.splice(index, 1);
    generatePreviews(items.value);
    emit('update:modelValue', items.value);
};

const openFilePicker = () => {
    (fileInput.value as HTMLInputElement)?.click();
};
</script>

<template>
    <Label class="text-sm">{{ $t(label) }}</Label>

    <p class="text-secondary-text text-xs font-semibold">{{ $t(hint) }}</p>

    <div class="flex items-start justify-start gap-x-2.5">
        <div class="flex flex-wrap gap-2.5">
            <div v-for="(preview, index) in previews" :key="'preview-' + index" class="relative h-30 w-30 overflow-hidden rounded border">
                <img :src="preview" alt="Preview" class="h-full w-full object-cover" />
                <button
                    class="bg-opacity-50 absolute top-0 right-0 cursor-pointer rounded-bl px-1 py-0.5 text-xs text-white hover:text-red-500"
                    @click.prevent="removeImage(index)"
                >
                    <Icon icon="lucide:x" class="h-4 w-4" />
                </button>
            </div>

            <div>
                <div
                    v-if="!maxItems || items.length < maxItems"
                    @click="openFilePicker"
                    class="border-button-border flex h-30 w-30 cursor-pointer items-center justify-center rounded-md border-2 border-dashed p-4 text-center text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950"
                >
                    <Icon icon="lucide:image" class="text-sidebar-border h-[30px] w-[35px]" />
                </div>

                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    multiple
                    @change="handleFileChange"
                    :disabled="maxItems ? items.length >= maxItems : false"
                    class="hidden"
                />
            </div>
        </div>
    </div>

    <div v-if="props.error" class="text-destructive fluid-text-xs-sm">{{ props.error }}</div>
</template>
