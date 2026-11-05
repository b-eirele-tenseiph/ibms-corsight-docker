<script setup lang="ts">
import { PaginateDotGooey } from '@/components/shared/pagination';
import { Carousel, CarouselContent, CarouselItem, type CarouselApi } from '@/components/ui/carousel';
import { Promo } from '@/types';
import { watchOnce } from '@vueuse/core';
import { ref } from 'vue';

const props = defineProps<{
    promos: Promo[];
}>();

const emblaMainApi = ref<CarouselApi>();
const selectedIndex = ref(0);

function onSelect() {
    if (!emblaMainApi.value) return;
    selectedIndex.value = emblaMainApi.value.selectedScrollSnap();
}

function onThumbClick(index: number) {
    if (!emblaMainApi.value) return;
    emblaMainApi.value.scrollTo(index);
    selectedIndex.value = index;
}

watchOnce(emblaMainApi, (embla) => {
    if (!embla) return;
    onSelect();

    embla.on('select', onSelect);
    embla.on('reInit', onSelect);
});
</script>

<template>
    <div class="w-full">
        <!-- Carousel -->
        <Carousel @init-api="(val) => (emblaMainApi = val)">
            <CarouselContent>
                <CarouselItem v-for="promo in props.promos" :key="promo.id" class="flex w-full">
                    <div
                        v-if="promo.photo_file_path"
                        class="relative flex w-full flex-col justify-center overflow-hidden rounded-t-[10px]"
                        :alt="promo.title"
                    >
                        <!-- Blurred background -->
                        <div
                            class="absolute inset-0 scale-110 bg-cover bg-center blur-xl brightness-40 filter"
                            :style="{ backgroundImage: `url(${promo.photo_file_path})` }"
                        ></div>

                        <!-- Foreground content (sharp) -->
                        <div class="relative z-10 flex justify-center">
                            <img :src="promo.photo_file_path" class="xs:h-[50svh] h-[70svh] w-full object-contain" />
                        </div>
                    </div>
                </CarouselItem>
            </CarouselContent>
        </Carousel>

        <!-- Reusable Gooey Pagination -->
        <div class="bg-primary-text dark:bg-background flex justify-center rounded-b-md py-2">
            <PaginateDotGooey :length="props.promos.length" v-model="selectedIndex" @update:modelValue="onThumbClick" />
        </div>
    </div>
</template>
