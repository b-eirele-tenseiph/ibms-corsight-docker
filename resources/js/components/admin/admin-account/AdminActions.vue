<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';

defineEmits<{
    (e: 'add'): void;
    (e: 'import'): void;
}>();
</script>

<template>
    <div class="flex gap-2">
        <Button variant="default" @click="$emit('add')">
            <Icon icon="pepicons-pop:plus" class="size-4" /><span class="hidden md:flex">{{ $t('modal.action.add_admin') }}</span>
        </Button>
    </div>
</template>
