<script setup lang="ts">
import { useAttrs } from 'vue';

const props = defineProps<{
    icon?: string;
}>();

const attrs = useAttrs();

const fallbackLogo = '/images/Logo/Company/tensei-kanji-logo.png';
</script>

<template>
    <div>
        <img :src="props.icon || fallbackLogo" alt="App Logo" v-bind="attrs" class="block h-auto max-w-full" />
    </div>
</template>
