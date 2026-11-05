<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue';

defineProps<{
    page_title: string;
}>();
</script>

<template>
    <Label class="fluid-text-base-xl font-bold">
        {{ $te(page_title) ? $t(page_title) : page_title }}
    </Label>
</template>
