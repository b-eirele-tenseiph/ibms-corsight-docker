<script setup lang="ts">
import { AnimatedIcon } from '@/components/shared/icons/animated-icon';
</script>
<template>
    <div class="xxs:flex relative mt-8 hidden h-[50vh] sm:h-[60vh] lg:mt-0 lg:h-screen lg:w-13/24 lg:bg-none">
        <div class="bg-primary hidden w-3/8 rounded-r-[50px] lg:block"></div>
        <div
            class="absolute flex h-[200px] w-full justify-center place-self-center bg-[url(/images/Illustrations/Blob.svg)] bg-size-[70%_200px] bg-center bg-no-repeat p-10 pt-4 sm:h-[400px] sm:bg-size-[80%_400px] md:p-4 lg:bottom-0 lg:h-screen lg:bg-none lg:p-0"
        >
            <div class="lg:place-self-end">
                <img src="/images/Illustrations/Home Settings.svg" alt="App Logo" class="h-[20vh] sm:h-[30vh] lg:h-[60vh] xl:h-[70vh]" />
            </div>
        </div>
    </div>
    <div>
        <AnimatedIcon
            containerClass="top-[28%] left-[72%] sm:left-[75%] lg:top-[30%] xl:top-[22%] lg:left-[43%]"
            pingClass="bg-[#e0e0e0] [animation-delay:0.4s] w-10 sm:w-13 xl:w-16"
            iconClass="bg-[#e0e0e0] text-primary size-10 sm:size-13 xl:size-16"
            icon="lucide:house-plug"
        />

        <AnimatedIcon
            containerClass="top-[9%] left-[60%] lg:top-[48%] xl:top-[40%] lg:left-[32%]"
            pingClass="[animation-delay:0.2s] w-10 sm:w-13 xl:w-16"
            iconClass="size-10 sm:size-13 xl:size-16"
            icon="heroicons:light-bulb"
        />

        <AnimatedIcon
            containerClass="top-[20%] left-[15%] lg:left-[48%] lg:top-[60%] xl:top-[52%] "
            pingClass="w-10 sm:w-13 xl:w-16"
            iconClass="size-10 sm:size-13 xl:size-16"
            icon="lucide:air-vent"
        />
    </div>
</template>
