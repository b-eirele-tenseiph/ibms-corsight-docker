<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';

// Define emits instead of props
const emit = defineEmits<{
    (e: 'onAdd'): void;
}>();
</script>

<template>
    <div class="flex flex-row justify-end gap-2">
        <Button @click="emit('onAdd')" variant="default" type="button">
            <Icon icon="lucide:plus" width="18" height="18" />
            <span class="hidden text-sm font-semibold md:block">{{ $t('modal.action.add_amenity') }}</span>
        </Button>
    </div>
</template>
