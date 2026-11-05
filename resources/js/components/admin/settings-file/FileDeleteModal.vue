<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/settings/file';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    file?: { id: string; name: string };
}>();

const emit = defineEmits(['close']);

const onSubmit = () => {
    router.delete(destroy({ file: props.file!.id }).url, {
        onSuccess: handleClose,
    });
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <ModalForm
        :open="show"
        @close="handleClose"
        @submit="onSubmit"
        title="modal.action.delete_file"
        confirmLabel="action.delete"
        icon="bi:file-earmark-x"
        variant="destructive"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="font-semibold">{{ $t('modal.confirm.delete_file') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.delete_info') }}</p>

            <div class="flex flex-col gap-2">
                <label>
                    <span class="font-semibold">{{ $t('form.label.file_name') }}:</span> {{ file?.name }}
                </label>
            </div>
        </div>
    </ModalForm>
</template>
