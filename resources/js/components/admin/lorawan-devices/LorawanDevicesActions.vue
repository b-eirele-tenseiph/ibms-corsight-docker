<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { exportMethod, template } from '@/routes/admin/management/lorawan/devices';
import { ref } from 'vue';
import { DevicesImportModal } from '.';

const openImport = ref(false);

// event handlers for dropdown
const downloadTemplate = () => {
    location.href = template.url();
};
const triggerImportModal = () => {
    openImport.value = true;
};
const triggerExport = () => {
    location.href = exportMethod.url();
};
</script>

<template>
    <div class="flex gap-2">
        <DropdownOperation
            :operations="['template', 'import', 'export']"
            @template="downloadTemplate"
            @import="triggerImportModal"
            @export="triggerExport"
        />
        <DevicesImportModal :show="openImport" @close="openImport = false" />
    </div>
</template>
