<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Carousel, CarouselApi, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { UserPromoProps } from '@/types';
import { watchOnce } from '@vueuse/core';
import { ref } from 'vue';

defineProps<{
    promo: Array<UserPromoProps>;
}>();

const emblaMainApi = ref<CarouselApi>();
const emblaThumbnailApi = ref<CarouselApi>();
const selectedIndex = ref(0);

function onSelect() {
    if (!emblaMainApi.value || !emblaThumbnailApi.value) return;
    selectedIndex.value = emblaMainApi.value.selectedScrollSnap();
    emblaThumbnailApi.value.scrollTo(emblaMainApi.value.selectedScrollSnap());
}

function onThumbClick(index: number) {
    if (!emblaMainApi.value || !emblaThumbnailApi.value) return;
    emblaMainApi.value.scrollTo(index);
}

watchOnce(emblaMainApi, (emblaMainApi) => {
    if (!emblaMainApi) return;

    onSelect();
    emblaMainApi.on('select', onSelect);
    emblaMainApi.on('reInit', onSelect);
});
</script>

<template>
    <div class="flex h-full w-full flex-col items-center justify-center gap-2 overflow-hidden px-2 py-4 sm:gap-4 sm:px-4">
        <!-- Preview -->
        <div class="w-full max-w-5xl px-2">
            <Carousel class="w-full" @init-api="(val) => (emblaMainApi = val)">
                <CarouselContent>
                    <CarouselItem v-for="(promo, index) in promo" :key="index" class="flex items-center justify-center border-0">
                        <Card class="border-0 bg-transparent p-0 shadow-none">
                            <CardContent class="flex h-full items-center justify-center border-0 p-0 shadow-none sm:h-[60svh]">
                                <div class="group relative inline-block h-full">
                                    <img :src="promo.photo_file_path" :alt="promo.title" class="h-full w-auto rounded-lg object-contain" />
                                    <div
                                        class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-2 opacity-0 transition duration-200 ease-out group-focus-within:translate-y-0 group-focus-within:opacity-100 group-hover:translate-y-0 group-hover:opacity-100 md:translate-y-0 md:opacity-0"
                                        aria-hidden="false"
                                    >
                                        <div class="bg-accent inset-0 h-30 w-full backdrop-blur-sm"></div>
                                        <div
                                            class="text-primary-text absolute inset-0 bottom-0 flex flex-col justify-center gap-y-1 p-3 sm:gap-y-2 sm:p-6"
                                        >
                                            <h3 class="fluid-text-base-xl leading-tight font-semibold">
                                                {{ promo.title }}
                                            </h3>
                                            <div class="fluid-text-xs-sm line-clamp-2 leading-snug">
                                                {{ promo.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </CarouselItem>
                </CarouselContent>
            </Carousel>
        </div>

        <!-- Thumbnails -->
        <div class="w-full max-w-2xl px-12 md:px-0">
            <Carousel class="w-full" @init-api="(val) => (emblaThumbnailApi = val)">
                <CarouselContent class="ml-0 flex justify-center gap-1.5 sm:gap-2">
                    <CarouselItem
                        v-for="(promo, index) in promo"
                        :key="promo.id"
                        class="basis-1/3 cursor-pointer pl-0 sm:basis-1/4 md:basis-1/5"
                        @click="onThumbClick(index)"
                    >
                        <div
                            class="aspect-square rounded-md bg-cover bg-center bg-no-repeat transition-opacity duration-200 sm:rounded-lg"
                            :class="index === selectedIndex ? 'opacity-100' : 'brightness-50 hover:opacity-80'"
                            :style="{ backgroundImage: `url(${promo.photo_file_path})` }"
                        ></div>
                    </CarouselItem>
                </CarouselContent>
                <CarouselNext class="text-primary -right-12 sm:-right-14 md:-right-12" />
                <CarouselPrevious class="text-primary -left-12 sm:-left-14 md:-left-12" />
            </Carousel>
        </div>
    </div>
</template>
