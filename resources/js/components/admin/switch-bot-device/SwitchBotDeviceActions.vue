<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { exportMethod } from '@/routes/admin/management/switchbot/devices';

const triggerExport = () => {
    window.location.href = exportMethod().url;
};
</script>
<template>
    <div class="flex justify-between">
        <DropdownOperation :operations="['export']" @export="triggerExport" />
    </div>
</template>
