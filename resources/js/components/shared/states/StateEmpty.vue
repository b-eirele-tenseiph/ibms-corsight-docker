<script setup lang="ts">
import { Icon } from '@iconify/vue';

defineProps<{
    label?: string;
}>();
</script>

<template>
    <div class="text-secondary-muted-text fluid-text-xs-base flex flex-col items-center gap-2">
        <Icon icon="bytesize:inbox" class="size-14" />
        {{ $t(label || 'state.empty') }}
    </div>
</template>
