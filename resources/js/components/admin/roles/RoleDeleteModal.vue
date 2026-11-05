<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { deleteMethod } from '@/routes/admin/management/settings/roles';
import type { Permission, Role } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    role: Role | null;
    roles: Role[];
    permissions: Permission[];
    isOpen: boolean;
    isProcessing: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();
const deleteRole = reactive({
    form: useForm<{ ids: number[] }>({
        ids: [],
    }),
    confirmation: '',
    errorMessage: '',
    onCancel: () => {
        emit('close');
        deleteRole.confirmation = '';
        deleteRole.errorMessage = '';
    },
    onSave: () => {
        if (props.role && deleteRole.confirmation === props.role.name) {
            deleteRole.form.ids = [props.role.id];
            deleteRole.form.delete(deleteMethod({ query: { role: props.role.id } }).url, {
                onSuccess: () => {
                    deleteRole.onCancel();
                    emit('close');
                },
            });
        } else {
            deleteRole.errorMessage = 'Role name does not match or role name is missing.';
        }
    },
});
</script>

<template>
    <ModalForm
        :open="props.isOpen"
        confirmLabel="action.delete"
        title="modal.action.delete_role"
        :isProcessing="deleteRole.form.processing"
        @close="deleteRole.onCancel"
        @submit="deleteRole.onSave"
        icon="heroicons-outline:exclamation"
        :variant="'destructive'"
    >
        <form @submit.prevent="deleteRole.onSave">
            <div class="text-primary-text text-sm font-semibold">
                {{ $t('modal.confirm.delete_role') }}
            </div>
            <div class="text-destructive mt-2 text-sm font-semibold">
                {{ $t('modal.confirm.warning') }}
            </div>
            <div class="mt-2 flex items-center">
                <label class="text-primary-text text-sm font-semibold">{{ $t('form.label.role_name') }}:</label>
                <div class="text-primary-text ml-3">{{ props.role?.name }}</div>
            </div>
            <div class="mt-4">
                <InputWithLabel
                    v-model="deleteRole.confirmation"
                    label="form.label.role_name"
                    placeholder="form.placeholder.role_name"
                    :error="deleteRole.errorMessage"
                    class="placeholder:text-secondary-text placeholder:text-sm"
                />
            </div>
        </form>
    </ModalForm>
</template>
