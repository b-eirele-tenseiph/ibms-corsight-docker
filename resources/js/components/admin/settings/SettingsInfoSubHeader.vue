<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header>
        <h2 class="fluid-text-sm-base mb-1 font-bold">{{ $t(title) }}</h2>
        <p v-if="description" class="text-secondary-text fluid-text-xs-sm">
            {{ $t(description) }}
        </p>
    </header>
</template>
