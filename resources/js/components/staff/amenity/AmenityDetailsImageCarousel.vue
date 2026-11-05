<script setup lang="ts">
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { Dialog, DialogContent, DialogOverlay } from '@/components/ui/dialog';
import { AmenityPhoto } from '@/types';
import { ref, watch } from 'vue';

const props = defineProps<{
    photos: AmenityPhoto[];
    open: boolean;
    initialIndex: number;
}>();

const emit = defineEmits(['close']);

// Bind this to the carousel index
const currentSlide = ref(0);

// Set initial index on open
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            currentSlide.value = props.initialIndex;
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')" class="backdrop-blur-lg">
        <DialogOverlay class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs" />
        <DialogContent class="max-w-4xl border-none bg-transparent p-0 shadow-none">
            <Carousel v-model:currentSlide="currentSlide">
                <CarouselContent>
                    <CarouselItem v-for="photo in photos" :key="photo.id" class="flex items-center justify-center">
                        <div class="w-full max-w-screen-md">
                            <img :src="`${photo.file_path}`" alt="Carousel Photo" class="h-auto w-full object-contain" />
                        </div>
                    </CarouselItem>
                </CarouselContent>
                <CarouselPrevious />
                <CarouselNext />
            </Carousel>
        </DialogContent>
    </Dialog>
</template>
