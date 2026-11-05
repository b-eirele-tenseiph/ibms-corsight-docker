<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status: number;
    title: string;
    message: string;
}>();

const errorImages: Record<string, string> = {
    '404': '/images/Illustrations/Not Found.svg',
    '500': '/images/Illustrations/Server Error.svg',
};

const errorImage = computed(() => errorImages[props.status] || '/images/Illustrations/Cancel.svg');

const goBack = () => {
    router.visit('/');
};
</script>

<template>
    <Head :title="`${status} - ${title}`" />
    <div class="flex h-screen flex-col items-center justify-center gap-12 px-8 sm:px-12">
        <img :src="errorImage" class="mx-auto w-full max-w-[20rem] object-contain" />

        <div class="flex flex-col gap-4 text-center">
            <div class="flex items-center justify-center gap-2 font-bold">
                <span class="text-primary-text fluid-text-2xl-3xl">
                    <span class="text-primary font-black">{{ status }}</span>
                    {{ title }}
                </span>
            </div>
            <p class="text-primary-text fluid-text-sm-lg">{{ message }}</p>
        </div>

        <Button @click="goBack"><Icon icon="humbleicons:arrow-go-back" />{{ $t('action.back_to_home_page') }}</Button>
    </div>
</template>
