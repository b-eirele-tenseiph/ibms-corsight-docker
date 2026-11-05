<script setup lang="ts">
import { UserRoomAccess } from '@/components/admin/user-account';
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { DialogDescription } from '@/components/ui/dialog';
import { fromUtcToLocalParts } from '@/lib/utils';
import { roomAccessUpdate } from '@/routes/staff/dashboard';
import { AccountManagementUser, Room } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'close-all'): void;
}>();

const props = defineProps<{
    open: boolean;
    user: AccountManagementUser | null;
    rooms: Room[];
}>();

const editForm = useForm<{
    id: string;
    name: string;
    username: string;
    email: string;
    password: string;
    password_displayable: string;
    room_id: string;
    is_permanent: boolean;
    start_date: string;
    end_date: string;
    start_time: string;
    end_time: string;
    access_id: string;
}>({
    id: '',
    name: '',
    username: '',
    email: '',
    password: '',
    password_displayable: '',
    room_id: '',
    is_permanent: false,
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    access_id: '',
});

// Watch for incoming user data and set form values
watch(
    () => props.user,
    (user) => {
        if (!user) return;

        const { room_access } = user;

        // Handle access date/time conversion
        const localStart = room_access ? fromUtcToLocalParts(room_access.start_date) : { date: '', time: '' };
        const localEnd = room_access ? fromUtcToLocalParts(room_access.end_date) : { date: '', time: '' };

        editForm.defaults({
            id: user.id,
            name: user.name,
            username: user.username,
            email: user.email ?? '',
            password_displayable: user.password_displayable ?? '',
            room_id: room_access?.room_id ?? '',
            is_permanent: !!room_access?.is_permanent,
            start_date: localStart.date,
            start_time: localStart.time,
            end_date: localEnd.date,
            end_time: localEnd.time,
            access_id: room_access?.id ?? '',
        });

        editForm.reset(); // Apply defaults
    },
    { immediate: true },
);

// Submit the update for access
const submitUpdateAccess = () => {
    editForm.password = editForm.password_displayable;

    editForm.put(roomAccessUpdate({ access: editForm.access_id }).url, {
        onSuccess: () => {
            emit('close-all');
        },
    });
};

const { t } = useI18n();
const modalTitle = computed(() => {
    const room = props.rooms.find((r) => String(r.id) === editForm.room_id);
    return t('form.label.update_room_new_access', {
        room: room?.name || '',
    });
});

const hasSwitchBotLock = ref(false);

watch(
    () => editForm.room_id,
    (newValue) => {
        if (newValue) {
            const selectedRoom = props.rooms.find((r) => r.id === newValue);

            // ✅ Example: Trigger only if the room supports SwitchBot lock
            if (selectedRoom?.has_switch_bot_keypad_touch) {
                hasSwitchBotLock.value = true;
            } else {
                hasSwitchBotLock.value = false;
            }
        }
    },
);
onMounted(() => {
    const room = props.rooms.find((r) => r.id == editForm.room_id);
    hasSwitchBotLock.value = room?.has_switch_bot_keypad_touch;
});
</script>

<template>
    <ModalForm
        :open="open"
        :title="modalTitle"
        icon="fluent:key-multiple-24-regular"
        confirmLabel="action.update"
        variant="default"
        @submit="submitUpdateAccess"
        :blockers="!editForm.isDirty ? ['not-dirty'] : []"
        @close="$emit('close')"
    >
        <DialogDescription v-if="hasSwitchBotLock" class="fluid-text-xxs-xs text-info bg-info-bg mt-1 rounded-lg p-2 text-start">
            <div class="flex flex-row items-center space-x-1">
                <Icon icon="material-symbols:info-rounded" width="40" height="40" style="color: 'text-info'" />
                <a>
                    {{ $t('modal.description.issuance_message') }}
                </a>
            </div>
        </DialogDescription>
        <form @submit.prevent="submitUpdateAccess" class="mt-2 flex flex-col gap-y-4">
            <!-- Room Select -->
            <div class="flex flex-col gap-y-2">
                <InputSelectWithLabel
                    label="form.label.room_name"
                    placeholder="form.placeholder.room_name"
                    :items="props.rooms"
                    v-model:modelValue="editForm.room_id"
                    :error="editForm.errors.room_id"
                />
            </div>

            <!-- Name -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.name"
                    type="text"
                    placeholder="form.placeholder.name"
                    v-model:modelValue="editForm.name"
                    :error="editForm.errors.name"
                    readonly
                />
            </div>

            <!-- Email (optional) -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.email"
                    type="email"
                    placeholder="form.placeholder.email"
                    v-model:modelValue="editForm.email"
                    :error="editForm.errors.email"
                    readonly
                />
            </div>

            <div class="flex flex-col gap-y-2 sm:flex-row sm:gap-x-4">
                <!-- Username -->
                <div class="w-full">
                    <InputWithLabel
                        label="form.label.username"
                        type="text"
                        placeholder="form.placeholder.username"
                        v-model:modelValue="editForm.username"
                        :error="editForm.errors.username"
                        readonly
                    />
                </div>

                <!-- PIN -->
                <div class="w-full">
                    <InputWithLabel
                        label="form.label.access_pin"
                        type="password"
                        placeholder="form.placeholder.access_pin"
                        v-model:modelValue="editForm.password_displayable"
                        :error="editForm.errors.password"
                    />
                </div>
            </div>

            <!-- Access Dates -->
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
