<script setup lang="ts">
import UserRoomAccess from '@/components/admin/user-account/UserRoomAccess.vue';
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import { roomAccessNew } from '@/routes/staff/dashboard';
import { Room } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { CalendarDate, getLocalTimeZone, now, parseDate, Time } from '@internationalized/date';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

// Emits
const emit = defineEmits(['close', 'submit', 'close-all']);

// Props
const props = defineProps<{
    open: boolean;
    room: Room;
}>();

// Today and default values
const today = now(getLocalTimeZone()).toDate();
const startDate = new CalendarDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
const endDate = new CalendarDate(today.getFullYear() + 1, today.getMonth() + 1, today.getDate());
const currentTime = new Time(today.getHours(), today.getMinutes());

// Util to format CalendarDate to 'YYYY-MM-DD'
const formatCalendarDate = (date: CalendarDate): string => `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`;

// Util to format Time to 'HH:mm'
const formatTime = (time: Time): string => `${String(time.hour).padStart(2, '0')}:${String(time.minute).padStart(2, '0')}`;

// Form setup
const form = useForm({
    room_id: props.room.id,
    name: '',
    username: '',
    email: '',
    pin: '',
    password_displayable: '',
    is_permanent: false,
    start_date: formatCalendarDate(startDate),
    end_date: formatCalendarDate(endDate),
    start_time: formatTime(currentTime),
    end_time: formatTime(currentTime),
});

// Reset form when modal closes
watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            form.reset();
            form.clearErrors();
        } else {
            form.room_id = props.room.id;
        }
    },
);

// Submit handler
const submit = () => {
    form.password_displayable = form.pin;

    if (!form.is_permanent) {
        const parsedStartDate = parseDate(form.start_date);
        const parsedEndDate = parseDate(form.end_date);
        const startDateStr = formatCalendarDate(parsedStartDate);
        const endDateStr = formatCalendarDate(parsedEndDate);

        // Clean up time (strip seconds if present)
        const cleanStartTime = form.start_time.slice(0, 5);
        const cleanEndTime = form.end_time.slice(0, 5);

        form.start_date = startDateStr;
        form.end_date = endDateStr;
        form.start_time = cleanStartTime;
        form.end_time = cleanEndTime;
    }

    form.post(roomAccessNew({ room: props.room.id }).url, {
        onSuccess: () => {
            form.reset();
            emit('close');
            emit('close-all');
        },
    });
};

// Translations
const { t } = useI18n();
const modalTitle = computed(() => {
    return t('form.label.add_room_new_access', {
        room: props.room?.name || '',
    });
});
const hasSwitchBotLock = ref(props.room.has_switch_bot_keypad_touch);
</script>

<template>
    <ModalForm
        :open="open"
        :title="modalTitle"
        icon="fluent:key-multiple-24-regular"
        variant="default"
        confirmLabel="action.create"
        @submit="submit"
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

        <form @submit.prevent="submit" class="mt-2 flex flex-col gap-y-4">
            <!-- Name -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.name"
                    type="text"
                    placeholder="form.placeholder.name"
                    v-model:modelValue="form.name"
                    :error="form.errors.name"
                />
            </div>

            <!-- Username -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.username"
                    type="text"
                    placeholder="form.placeholder.username"
                    v-model:modelValue="form.username"
                    :error="form.errors.username"
                />
            </div>

            <!-- Email -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.email"
                    type="email"
                    placeholder="form.placeholder.email"
                    v-model:modelValue="form.email"
                    :error="form.errors.email"
                />
            </div>

            <!-- Access PIN -->
            <div class="flex flex-col gap-y-2">
                <InputWithLabel
                    label="form.label.access_pin"
                    type="password"
                    placeholder="form.placeholder.access_pin"
                    v-model:modelValue="form.pin"
                    :error="form.errors.pin"
                />
            </div>

            <!-- Access Schedule -->
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
