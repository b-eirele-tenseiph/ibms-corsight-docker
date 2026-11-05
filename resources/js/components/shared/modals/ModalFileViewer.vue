<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogOverlay, DialogTitle } from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

/* -------------------------------
 * Types
 * ------------------------------- */
export type FileLike =
    | File
    | {
          file_path?: string;
          file_type?: string;
          name?: string;
          [key: string]: any;
      };

type FileType = 'image' | 'video' | 'pdf' | 'file' | 'unknown';

/* -------------------------------
 * Props
 * ------------------------------- */
const props = defineProps<{
    open: boolean;
    file: FileLike | null;
    previewUrl?: string | null;
    title?: string;
    getFileType?: (file: FileLike) => FileType;
}>();

/* -------------------------------
 * Emits
 * ------------------------------- */
defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

/* -------------------------------
 * Logic
 * ------------------------------- */
const resolveFileType = (file: FileLike): FileType => {
    if (!file) return 'unknown';

    // 1. External override
    if (props.getFileType) return props.getFileType(file);

    // 2. Native File object
    if (file instanceof File) {
        if (file.type.startsWith('image/')) return 'image';
        if (file.type.startsWith('video/')) return 'video';
        if (file.type === 'application/pdf') return 'pdf';
        return 'file';
    }

    // 3. Custom FileLike (Backend Object)
    if ((file as any).file_type) {
        const t = (file as any).file_type.toLowerCase();
        if (t.includes('image')) return 'image';
        if (t.includes('video')) return 'video';
        if (t.includes('pdf')) return 'pdf';
        return 'file';
    }

    // 4. Fallback: infer from URL extension
    const url = typeof file === 'string' ? file : ((file as any).file_path ?? '');
    const ext = url.split('.').pop()?.toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext!)) return 'image';
    if (['mp4', 'webm', 'mov', 'avi'].includes(ext!)) return 'video';
    if (ext === 'pdf') return 'pdf';

    return 'file';
};

const currentFileType = computed(() => (props.file ? resolveFileType(props.file) : 'unknown'));

const fileName = computed(() => {
    if (!props.file) return '';
    if (props.file instanceof File) return props.file.name;
    return (props.file as any).name || 'Attachment';
});
</script>

<template>
    <Dialog :open="props.open" @update:open="$emit('update:open', $event)">
        <DialogOverlay class="bg-black/40 backdrop-blur-sm transition-all duration-300" />
        <DialogContent class="max-w-6xl overflow-hidden border-none bg-white p-0 shadow-2xl ring-1 ring-black/5 outline-none sm:rounded-2xl">
            <DialogTitle class="sr-only">{{ title || fileName }}</DialogTitle>
            <div
                class="absolute top-0 left-0 z-50 flex w-full items-center justify-between border-b border-gray-100 bg-white/80 px-4 py-3 backdrop-blur-md transition-all"
            >
                <div class="flex items-center gap-2">
                    <!-- Icon Bubble -->
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600">
                        <Icon v-if="currentFileType === 'image'" icon="lucide:image" class="h-4 w-4" />
                        <Icon v-else-if="currentFileType === 'video'" icon="lucide:video" class="h-4 w-4" />
                        <Icon v-else-if="currentFileType === 'pdf'" icon="lucide:file-text" class="h-4 w-4" />
                        <Icon v-else icon="lucide:file" class="h-4 w-4" />
                    </div>
                    <!-- Filename -->
                    <span class="max-w-[300px] truncate text-sm font-medium text-zinc-800 antialiased">
                        {{ fileName }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- External Link -->
                    <a
                        v-if="props.previewUrl"
                        :href="props.previewUrl"
                        target="_blank"
                        class="rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-zinc-900"
                        title="Open in new tab"
                    >
                        <Icon icon="lucide:download" class="h-5 w-5" />
                    </a>

                    <!-- Close Button -->
                    <DialogClose
                        class="group flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-gray-100 transition-all hover:bg-gray-200 focus:outline-none"
                    >
                        <Icon icon="lucide:x" class="h-4 w-4 text-gray-600 group-hover:text-gray-900" />
                    </DialogClose>
                </div>
            </div>

            <div class="relative flex max-h-[80vh] min-h-[50vh] w-full items-center justify-center bg-gray-50/50">
                <!-- Loading -->
                <div v-if="!file" class="text-muted-foreground flex flex-col items-center">
                    <Icon icon="lucide:loader-2" class="h-8 w-8 animate-spin text-gray-400" />
                </div>

                <!-- IMAGE -->
                <img
                    v-else-if="currentFileType === 'image'"
                    :src="props.previewUrl!"
                    class="h-full min-h-[50vh] w-full object-contain p-4 transition-opacity duration-300"
                    alt="Preview"
                />

                <!-- VIDEO -->
                <video
                    v-else-if="currentFileType === 'video'"
                    :src="props.previewUrl!"
                    controls
                    autoplay
                    class="min-h-[50vh] w-full max-w-full rounded-lg outline-none"
                />

                <!-- PDF -->
                <div v-else-if="currentFileType === 'pdf'" class="h-full w-full bg-gray-100 pt-[60px]">
                    <iframe :src="props.previewUrl!" class="min-h-[80vh] w-full border-none" />
                </div>

                <!-- FALLBACK -->
                <div v-else class="flex flex-col items-center justify-center text-gray-500">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-100 ring-1 ring-gray-200">
                        <Icon icon="lucide:file-question" class="h-10 w-10 text-gray-400" />
                    </div>
                    <p class="text-sm font-medium text-gray-900">{{ $t('modal.alert.preview_unavailable') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $t('modal.alert.cannot_view') }}</p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
