<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Separator } from '@/components/ui/separator';
import { forceDelete } from '@/routes/admin/management/settings/file';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    file?: { id: string; name: string };
}>();

const emit = defineEmits(['close']);

const form = useForm({
    confirmation: '',
});

const onSubmit = () => {
    if (!props.file?.id) return;

    form.delete(forceDelete({ file: props.file.id }).url, {
        onSuccess: handleClose,
    });
};

const handleClose = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <ModalForm
        :open="show"
        @close="handleClose"
        @submit="onSubmit"
        title="modal.action.permanently_delete_file"
        confirmLabel="action.delete"
        icon="bi:file-earmark-x"
        variant="destructive"
        :isBlocked="form.processing"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="font-semibold">{{ $t('modal.confirm.delete_file_permanently') }}</p>
            <p class="font-semibold text-red-600">{{ $t('modal.confirm.warning') }}</p>

            <div class="flex flex-col gap-2">
                <label>
                    <span class="font-semibold">{{ $t('form.label.file_name') }}:</span> {{ file?.name }}
                </label>
            </div>

            <Separator />

            <InputWithLabel v-model="form.confirmation" label="modal.confirm.delete_input" placeholder="DELETE" :error="form.errors.confirmation" />
        </div>
    </ModalForm>
</template>
