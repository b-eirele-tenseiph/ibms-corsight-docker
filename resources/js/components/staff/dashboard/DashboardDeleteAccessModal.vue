<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { roomAccessDelete } from '@/routes/staff/dashboard';
import { RoomAccessWithUser } from '@/types';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'close-all']);

const props = defineProps<{
    open: boolean;
    access: RoomAccessWithUser | null;
}>();
// DELETE ACCESS
const submitDeleteAccess = () => {
    if (!props.access) return;

    useForm({}).delete(roomAccessDelete({ access: props.access.id }).url, {
        onSuccess: () => {
            emit('close');
            emit('close-all');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="form.label.delete_room_access"
        icon="lucide:triangle-alert"
        confirmLabel="action.delete"
        variant="destructive"
        @close="$emit('close')"
        @submit="submitDeleteAccess"
        iconColor="text-error"
    >
        <form @submit.prevent="submitDeleteAccess" class="flex flex-col gap-y-4">
            <div class="text-sm font-bold">
                <p>
                    {{ $t('modal.confirm.delete_room_access') }}
                </p>
            </div>

            <div class="flex flex-col gap-y-1.5 text-sm">
                <p class="text-error text-sm font-semibold">
                    {{ $t('modal.confirm.warning') }}
                </p>
                <p class="font-regular">
                    <span class="font-semibold">{{ $t('form.label.name') }}:</span>
                    {{ props.access?.user.name }}
                </p>
            </div>
        </form>
    </ModalForm>
</template>
