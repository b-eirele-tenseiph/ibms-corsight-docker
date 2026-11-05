<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { Button } from '@/components/ui/button';
import { exportMethod, generateTemplate } from '@/routes/admin/management/switchbot/accounts';
import { QueryParams, Room } from '@/types';
import { Icon } from '@iconify/vue';
import { RoomFilter, Search } from '.';

defineProps<{
    query: QueryParams;
    rooms: Array<Room>;
}>();

const emit = defineEmits<{
    (e: 'open'): void;
    (e: 'importModal'): void;
}>();

// event handlers for dropdown
const downloadTemplate = () => {
    window.location.href = generateTemplate.url();
};

const triggerImportModal = () => {
    emit('importModal');
};

const triggerExport = () => {
    window.location.href = exportMethod.url();
};
</script>
<template>
    <div class="flex flex-row justify-between gap-4">
        <div class="xs:flex-row flex w-full flex-col gap-2">
            <Search :query="query" />
            <div class="w-full max-w-50">
                <RoomFilter :item="rooms" :query="query" />
            </div>
        </div>
        <div class="flex flex-row justify-end gap-2">
            <Button variant="default" @click="$emit('open')">
                <Icon icon="lucide:plus" class="size-4" />
                <span class="hidden md:flex">{{ $t('modal.action.add_account') }}</span></Button
            >
            <DropdownOperation
                :operations="['template', 'import', 'export']"
                @template="downloadTemplate"
                @import="triggerImportModal"
                @export="triggerExport"
            />
        </div>
    </div>
</template>
