<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/accounts/users';
import { type AccountManagementUser } from '@/types';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close']);

const props = defineProps<{
    open: boolean;
    user: AccountManagementUser | null;
}>();

// DELETE USER
const submitDeleteUser = () => {
    if (!props.user) return;

    useForm({}).delete(destroy.url({ user: props.user.id }), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.delete_user"
        icon="lucide:triangle-alert"
        confirmLabel="action.delete"
        variant="destructive"
        @close="$emit('close')"
        @submit="submitDeleteUser"
        iconColor="text-error"
    >
        <form @submit.prevent="submitDeleteUser" class="flex flex-col gap-y-4">
            <div class="text-sm font-bold">
                <p>
                    {{ $t('modal.confirm.delete_user') }}
                </p>
            </div>

            <div class="flex flex-col gap-y-1.5 text-sm">
                <p class="text-error text-sm font-semibold">
                    {{ $t('modal.confirm.warning') }}
                </p>
                <p class="font-regular">
                    <span class="font-semibold">{{ $t('form.label.username') }}:</span>
                    {{ props.user?.username }}
                </p>
                <p class="font-regular">
                    <span class="font-semibold">{{ $t('form.label.name') }}:</span>
                    {{ props.user?.name }}
                </p>
            </div>
        </form>
    </ModalForm>
</template>
