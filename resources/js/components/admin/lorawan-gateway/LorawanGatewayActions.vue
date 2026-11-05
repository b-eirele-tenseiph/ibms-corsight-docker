<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { Button } from '@/components/ui/button';
import { exportMethod, importTemplate } from '@/routes/admin/management/lorawan/gateways';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import { GatewayImportModal } from '.';

const openImport = ref(false);

const getTemplate = () => {
    window.location.href = importTemplate.url();
};

const importModal = () => {
    openImport.value = true;
};

const exportGateway = () => {
    window.location.href = exportMethod.url();
};
</script>

<template>
    <div class="flex gap-2">
        <Button variant="default" @click="$emit('add')">
            <Icon icon="pepicons-pop:plus" class="size-4" /><span class="hidden md:flex">{{ $t('modal.action.add_gateway') }}</span>
        </Button>
        <DropdownOperation :operations="['template', 'import', 'export']" @template="getTemplate" @import="importModal" @export="exportGateway" />
        <GatewayImportModal :show="openImport" @close="openImport = false" />
    </div>
</template>
