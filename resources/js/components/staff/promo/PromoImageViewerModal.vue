<script setup lang="ts">
import { Dialog, DialogContent, DialogOverlay } from '@/components/ui/dialog';
import { Promo } from '@/types';

defineProps<{
    promo: Promo | null;
    open: boolean;
}>();

const emit = defineEmits(['close']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogOverlay class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs" />

        <DialogContent class="max-w-4xl border-0 bg-transparent p-0 shadow-none focus:outline-none">
            <div class="flex items-center justify-center">
                <img v-if="promo?.photo_file_path" :src="`${promo.photo_file_path}`" alt="Promo" class="max-h-[80vh] shadow-lg" />
            </div>
        </DialogContent>
    </Dialog>
</template>
