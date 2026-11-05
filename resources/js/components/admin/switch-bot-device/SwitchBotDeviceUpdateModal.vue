<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/admin/management/switchbot/devices';
import { DeviceRoom, SwitchBotDeviceData, SwitchBotDeviceUpdateFormData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// Props passed into the modal component
const props = defineProps<{
    show: boolean;
    device: SwitchBotDeviceData;
    rooms: Array<DeviceRoom>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

// Form setup initialized with values from the selected device
const form = useForm<SwitchBotDeviceUpdateFormData>({
    id: props.device.id,
    name: props.device.name,
    room_area_id: props.device.room_area.id,
});

// Computed flag to disable the submit button if no changes made
const isBlocked = computed(() => form.processing || !form.isDirty);

// Get the current room based on the device's room_area.room_id
const currentRoom = computed(() => props.rooms.find((r) => r.id === props.device.room?.id));

// Room areas for the current room only, formatted for the select component
const roomAreaItems = computed(() => {
    if (!currentRoom.value?.room_areas) return [];
    return currentRoom.value.room_areas.map((area: any) => ({ ...area }));
});

// Handles form submission to update the device
function handleSubmit() {
    if (!form.isDirty) return;
    form.put(update({ device: props.device.id }).url, {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
}
</script>

<template>
    <ModalForm
        :open="show"
        @close="emit('close')"
        @submit="handleSubmit"
        title="modal.action.update_switch_bot_device"
        icon="fluent:iot-16-regular"
        confirmLabel="action.update"
        variant="default"
        :closeOnOverlayClick="true"
        :isBlocked="isBlocked"
    >
        <form @submit.prevent="handleSubmit" class="max-h-full space-y-4">
            <!-- Room -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.room_name"
                    placeholder="form.label.room_name"
                    :model-value="currentRoom?.name || ''"
                    :disabled="true"
                />
            </div>
            <!-- Room Area -->
            <div class="grid gap-1.5">
                <InputSelectWithLabel
                    label="form.label.room_area"
                    placeholder="form.placeholder.room_area"
                    v-model="form.room_area_id"
                    :items="roomAreaItems"
                    :error="form.errors.room_area_id"
                />
            </div>
            <!-- Device Name -->
            <div class="grid gap-1.5">
                <InputWithLabel label="form.label.device_name" placeholder="form.label.device_name" v-model="form.name" :error="form.errors.name" />
            </div>
        </form>
    </ModalForm>
</template>
