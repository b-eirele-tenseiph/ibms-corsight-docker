<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { Button } from '@/components/ui/button';
import { exportMethod, importTemplate } from '@/routes/admin/management/rooms';
import { Icon } from '@iconify/vue';

defineEmits<{
    (e: 'add'): void;
    (e: 'import'): void;
}>();

const exportRoom = () => {
    window.location.href = exportMethod.url();
};

const getTemplate = () => {
    window.location.href = importTemplate.url();
};
</script>

<template>
    <div class="flex gap-2">
        <Button variant="default" @click="$emit('add')">
            <Icon icon="pepicons-pop:plus" class="size-4" />
            <span class="hidden md:flex">{{ $t('modal.action.add_room') }}</span>
        </Button>
        <DropdownOperation :operations="['template', 'import', 'export']" @template="getTemplate" @import="$emit('import')" @export="exportRoom" />
    </div>
</template>
