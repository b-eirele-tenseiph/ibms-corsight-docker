<script setup lang="ts">
import { DropdownOperation } from '@/components/shared/dropdowns';
import { Button } from '@/components/ui/button';
import { exportMethod } from '@/routes/admin/management/accounts/users';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    onAdd: () => void;
}>();

const triggerExport = () => {
    window.location.href = exportMethod().url;
};
</script>

<template>
    <div class="flex flex-row justify-end gap-2">
        <Button @click="props.onAdd" variant="default" type="button">
            <Icon icon="lucide:plus" width="18" height="18" />
            <span class="hidden text-sm font-semibold md:block">{{ $t('modal.action.add_user') }}</span>
        </Button>

        <DropdownOperation :operations="['export']" @export="triggerExport" />
    </div>
</template>
