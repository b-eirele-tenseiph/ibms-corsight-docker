<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { exportMethod } from '@/routes/admin/management/logs';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    queryParams: object;
}>();

const exportRemote = () => (window.location.href = exportMethod({ query: { ...props.queryParams, page: undefined } }).url);
</script>
<template>
    <Button variant="outline" @click="exportRemote">
        <span class="hidden md:flex">{{ $t('operation.export') }}</span>
        <Icon icon="heroicons-outline:external-link" class="text-secondary-text size-4" />
    </Button>
</template>
