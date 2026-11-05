<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import { formatTime, parseTimeString } from '@/lib/utils';
import { store } from '@/routes/admin/management/accounts/users';
import { Room } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { CalendarDate, getLocalTimeZone, now, parseDate, Time } from '@internationalized/date';
import { ref, watch } from 'vue';
import UserRoomAccess from './UserRoomAccess.vue';

const emit = defineEmits(['close', 'submit']);
const props = defineProps<{
    open: boolean;
    rooms: Room[];
}>();

const today = now(getLocalTimeZone()).toDate();
const startDate = new CalendarDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
const endDate = new CalendarDate(today.getFullYear() + 1, today.getMonth() + 1, today.getDate());

const currentHour = today.getHours();
const currentMinute = today.getMinutes();
const currentTime = new Time(currentHour, currentMinute);

const form = useForm({
    room_id: '',
    name: '',
    username: '',
    email: '',
    password: '',
    password_displayable: '',
    is_permanent: false,
    start_date: startDate.toString(),
    end_date: endDate.toString(),
    start_time: formatTime(currentTime),
    end_time: formatTime(currentTime),
});

// Watch for modal open state; clear validation errors when modal is closed
watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            form.clearErrors();
        }
    },
);

const hasSwitchBotLock = ref(false);

watch(
    () => form.room_id,
    (newValue) => {
        if (newValue) {
            const selectedRoom = props.rooms.find((r) => r.id === newValue);

            // Trigger only if the room supports SwitchBot lock
            if (selectedRoom?.has_switch_bot_keypad_touch) {
                hasSwitchBotLock.value = true;
            } else {
                hasSwitchBotLock.value = false;
            }
        }
    },
);

const submit = () => {
    form.password_displayable = form.password;

    if (!form.is_permanent) {
        const startDate = parseDate(form.start_date);
        const endDate = parseDate(form.end_date);
        const startTime = parseTimeString(form.start_time);
        const endTime = parseTimeString(form.end_time);

        form.start_date = startDate.toString();
        form.end_date = endDate.toString();

        form.start_time = startTime.toString();
        form.end_time = endTime.toString();
    }

    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <ModalForm
        :open="open"
        title="modal.action.add_user"
        icon="lucide:user-lock"
        variant="default"
        confirmLabel="action.create"
        @submit="submit"
        @close="$emit('close')"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-y-4">
            <DialogDescription v-if="hasSwitchBotLock" class="fluid-text-xxs-xs text-info bg-info-bg mt-1 rounded-lg p-2 text-start">
                <div class="flex flex-row items-center space-x-1">
                    <Icon icon="material-symbols:info-rounded" width="40" height="40" style="color: 'text-info'" />
                    <a>
                        {{ $t('modal.description.issuance_message') }}
                    </a>
                </div>
            </DialogDescription>
            <div class="flex flex-col gap-y-2">
                <InputSelectWithLabel
                    label="form.label.room_name"
                    placeholder="form.placeholder.room_name"
                    :items="props.rooms"
                    v-model:modelValue="form.room_id"
                    :error="form.errors.room_id"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.name"
                    type="text"
                    placeholder="form.placeholder.name"
                    v-model:modelValue="form.name"
                    :error="form.errors.name"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.username"
                    type="text"
                    placeholder="form.placeholder.username"
                    v-model:modelValue="form.username"
                    :error="form.errors.username"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.email_address"
                    type="email"
                    placeholder="form.placeholder.email_address"
                    v-model:modelValue="form.email"
                    :error="form.errors.email"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.access_pin"
                    type="password"
                    placeholder="form.placeholder.access_pin"
                    v-model:modelValue="form.password"
                    :error="form.errors.password"
                />
            </div>

            <div class="flex flex-col gap-y-2">
                <UserRoomAccess
                    v-model:isPermanent="form.is_permanent"
                    v-model:startDate="form.start_date"
                    v-model:endDate="form.end_date"
                    v-model:startTime="form.start_time"
                    v-model:endTime="form.end_time"
                    :startDateError="form.errors.start_date"
                    :endDateError="form.errors.end_date"
                    :startTimeError="form.errors.start_time"
                    :endTimeError="form.errors.end_time"
                />
            </div>
        </form>
    </ModalForm>
</template>
