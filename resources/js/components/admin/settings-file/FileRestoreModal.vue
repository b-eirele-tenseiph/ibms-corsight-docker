<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { restore } from '@/routes/admin/management/settings/file';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    file?: any;
}>();

const emit = defineEmits(['close']);

const onSubmit = () => {
    router.post(
        restore({ file: props.file?.id }).url,
        {},
        {
            onSuccess: () => emit('close'),
        },
    );
};
</script>

<template>
    <ModalForm
        :open="show"
        @close="emit('close')"
        @submit="onSubmit"
        title="modal.action.restore_file"
        confirmLabel="action.restore"
        icon="ic:round-restore"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="font-semibold">{{ $t('modal.confirm.restore_file') }}</p>

            <div class="flex flex-col gap-2">
                <label>
                    <span class="font-semibold">{{ $t('form.label.file_name') }}:</span> {{ file?.name }}
                </label>
            </div>
        </div>
    </ModalForm>
</template>
