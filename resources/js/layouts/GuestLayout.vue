<script setup lang="ts">
import { AppLogo } from '@/components/shared/icons/logo';
import { Link } from '@inertiajs/vue3';

const icon = '/images/Logo/Company/tensei-kanji-logo.png';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-10 pt-6 sm:px-5 sm:pt-0">
        <div>
            <Link href="/">
                <AppLogo :icon="icon" width="150" />
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden rounded-sm bg-white px-6 py-4 shadow-sm sm:max-w-md sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
