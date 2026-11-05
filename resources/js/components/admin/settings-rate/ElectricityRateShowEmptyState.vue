<script setup lang="ts">
import { Icon } from '@iconify/vue';
</script>

<template>
    <div class="gap-2 py-6 text-center">
        <Icon icon="tabler:currency-peso" class="text-secondary-muted-text mx-auto size-8" />
        <p class="text-secondary-text fluid-text-xs-sm">{{ $t('page.rate_settings.label.no_rate') }}</p>
    </div>
</template>
