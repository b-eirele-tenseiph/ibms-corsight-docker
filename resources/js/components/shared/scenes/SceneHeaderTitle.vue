<script setup lang="ts">
import { Label } from '@/components/ui/label';

defineProps<{
    title: string;
}>();
</script>

<template>
    <div class="space-y-2 p-6">
        <Label class="text-sm font-semibold sm:text-base">{{ $t(`modal.action.${title}`) }}</Label>
        <Label class="text-secondary-text fluid-text-xs-sm leading-4">{{ $t('modal.description.scene_description') }}</Label>
    </div>
</template>
