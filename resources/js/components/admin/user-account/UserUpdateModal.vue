<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { DialogDescription } from '@/components/ui/dialog';
import { fromUtcToLocalParts } from '@/lib/utils';
import { update } from '@/routes/admin/management/accounts/users';
import type { AccountManagementUser } from '@/types';
import { Room } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import UserRoomAccess from './UserRoomAccess.vue';

// Emit 'close' event to parent when modal closes or user is updated
const emit = defineEmits(['close']);

// Props received from parent
const props = defineProps<{
    open: boolean;
    user: AccountManagementUser | null;
    rooms: Room[];
}>();

// Initialize form with user data or empty defaults
const editForm = useForm({
    id: props.user?.id ?? '',
    name: props.user?.name ?? '',
    username: props.user?.username ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_displayable: '',
    room_id: props.user?.room_access?.room_id ?? '',
    is_permanent: !!props.user?.room_access?.is_permanent,
    start_date: props.user?.room_access?.start_date ?? '',
    end_date: props.user?.room_access?.end_date ?? '',
    start_time: props.user?.room_access?.start_time ?? '',
    end_time: props.user?.room_access?.end_time ?? '',
});

const hasSwitchBotLock = ref(false);

// Watch for changes to `props.user` and update form accordingly
watch(
    () => props.user,
    (user) => {
        if (!user) return;

        // Populate base fields
        Object.assign(editForm, {
            ...user,
            room_id: user.room_access?.room_id,
            is_permanent: !!user.room_access?.is_permanent,
        });

        const room = props.rooms.find((r) => (r.id = editForm.room_id));
        hasSwitchBotLock.value = room?.has_switch_bot_keypad_touch;

        // Extract date and time from datetime strings
        if (user.room_access) {
            const { start_date, end_date } = user.room_access;

            // Converts a UTC datetime string from DB to local date and time parts.
            const localStart = fromUtcToLocalParts(start_date);
            const localEnd = fromUtcToLocalParts(end_date);

            editForm.start_date = localStart.date;
            editForm.start_time = localStart.time;

            editForm.end_date = localEnd.date;
            editForm.end_time = localEnd.time;
        }
    },
    { immediate: true }, // Trigger immediately on mount
);

// Watch for modal open state; clear validation errors when modal is closed
watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            editForm.clearErrors();
        }
    },
);

// Submit updated user data to the server
const submitEditUser = () => {
    editForm.password = editForm.password_displayable;

    editForm.put(update({ user: editForm.id }).url, {
        // Close modal on success
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <!-- Modal wrapper for editing user account -->
    <ModalForm
        :open="open"
        title="modal.action.update_user"
        icon="lucide:user-lock"
        confirmLabel="action.update"
        variant="default"
        @submit="submitEditUser"
        :blockers="!editForm.isDirty ? ['not-dirty'] : []"
        @close="$emit('close')"
    >
        <!-- Main form -->
        <form @submit.prevent="submitEditUser" class="flex flex-col gap-y-4">
            <DialogDescription v-if="hasSwitchBotLock" class="fluid-text-xxs-xs text-info bg-info-bg mt-1 rounded-lg p-2 text-start">
                <div class="flex flex-row items-center space-x-1">
                    <Icon icon="material-symbols:info-rounded" width="40" height="40" style="color: 'text-info'" />
                    <a>
                        {{ $t('modal.description.issuance_message') }}
                    </a>
                </div>
            </DialogDescription>
            <!-- Name input -->
            <div class="mt-2 flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.name"
                    type="text"
                    placeholder="form.placeholder.name"
                    v-model:modelValue="editForm.name"
                    :error="editForm.errors.name"
                />
            </div>

            <!-- Username input -->
            <div class="flex flex-col gap-y-4">
                <InputWithLabel
                    label="form.label.username"
                    type="text"
                    placeholder="form.placeholder.username"
                    v-model:modelValue="editForm.username"
                    :error="editForm.errors.username"
                />
            </div>

            <!-- Email input -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.email_address"
                    type="email"
                    placeholder="form.placeholder.email_address"
                    v-model:modelValue="editForm.email"
                    :error="editForm.errors.email"
                />
            </div>

            <!-- PIN / password input -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.access_pin"
                    type="password"
                    placeholder="form.placeholder.access_pin"
                    v-model:modelValue="editForm.password_displayable"
                    :error="editForm.errors.password"
                />
            </div>

            <!-- Room access configuration (permanent or date/time range) -->
            <div class="flex flex-col gap-y-2">
                <UserRoomAccess
                    v-model:isPermanent="editForm.is_permanent"
                    v-model:startDate="editForm.start_date"
                    v-model:endDate="editForm.end_date"
                    v-model:startTime="editForm.start_time"
                    v-model:endTime="editForm.end_time"
                    :startDateError="editForm.errors.start_date"
                    :endDateError="editForm.errors.end_date"
                    :startTimeError="editForm.errors.start_time"
                    :endTimeError="editForm.errors.end_time"
                />
            </div>
        </form>
    </ModalForm>
</template>
