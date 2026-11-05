<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/admin/management/lorawan/devices';
import { Gateway, LorawanDeviceData, Manufacturer, Room, RoomArea } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    deviceData: LorawanDeviceData | null;
    manufacturers: Array<Manufacturer>;
    gateways: Array<Gateway>;
    rooms: Array<Room>;
    roomAreas: Array<RoomArea>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();

// Define the form state
const form = useForm({
    name: '',
    dev_eui: '',
    manufacturer_id: props.deviceData?.manufacturer_id,
    model: props.deviceData?.model,
    gateway_id: props.deviceData?.gateway_id,
    room_id: props.deviceData?.room_id,
    room_area_id: props.deviceData?.room_area_id,
    online_flag: props.deviceData?.online_flag,
});

// Selected manufacturer & filtered models
const selectedManufacturer = computed(() => props.manufacturers.find((m) => m.id === form.manufacturer_id));
const filteredModels = computed(() => selectedManufacturer.value?.models ?? []);

watch(
    () => form.manufacturer_id,
    () => {
        if (!filteredModels.value.some((m) => m.name === form.model)) {
            form.model = null;
        }
    },
);

// Room Areas (filtered by room)
const filteredRoomAreas = computed(() => {
    if (!form.room_id) return [];
    return props.roomAreas.filter((area) => area.room_id === form.room_id);
});

// Reset room_area_id when room changes
watch(
    () => form.room_id,
    () => {
        if (!filteredRoomAreas.value.some((a) => a.id === form.room_area_id)) {
            form.room_area_id = null;
        }
    },
);

// When deviceData is loaded (Edit modal opens)
watch(
    () => props.deviceData,
    (device) => {
        if (!device) return;

        form.defaults({
            name: device.name,
            dev_eui: device.dev_eui,
            manufacturer_id: device.manufacturer_id,
            model: device.model,
            gateway_id: device.gateway_id,
            room_id: device.room_id,
            room_area_id: device.room_area_id,
            online_flag: device.online_flag ?? !!device.room_id,
        });
        form.reset();
    },
    { immediate: true },
);

// Cancel action (reset form and close)
function onCancel() {
    form.reset();
    form.clearErrors();
    emit('close');
}

// Submit the form to update the device
function onSubmit() {
    if (!props.deviceData?.id) return;
    form.put(update(props.deviceData.id).url, {
        onSuccess: () => {
            form.reset();
            emit('submit');
            emit('close');
        },
    });
}
</script>

<template>
    <ModalForm
        :open="props.isOpen"
        title="modal.action.update_lorawan_device"
        confirmLabel="action.update"
        :isProcessing="form.processing"
        @close="onCancel"
        @submit="onSubmit"
        icon="tabler:bulb"
        iconStyle="text-primary-text h-6 w-6"
        variant="default"
        :isBlocked="form.processing || !form.isDirty"
    >
        <div class="max-h-[60vh] space-y-4 pr-2">
            <!-- Device Name -->
            <InputWithLabel
                v-model="form.name"
                label="form.label.device_name"
                placeholder="form.placeholder.enter_device_name"
                :error="form.errors.name"
            />

            <!-- Device EUI -->
            <InputWithLabel
                v-model="form.dev_eui"
                label="form.label.dev_eui"
                placeholder="form.placeholder.enter_device_eui"
                :error="form.errors.dev_eui"
            />

            <!-- Manufacturer -->
            <InputSelectWithLabel
                v-model="form.manufacturer_id"
                :items="props.manufacturers"
                label="form.label.manufacturer"
                placeholder="form.placeholder.manufacturer"
                :error="form.errors.manufacturer_id"
            />

            <!-- Model -->
            <InputSelectWithLabel
                v-if="filteredModels.length"
                v-model="form.model"
                :items="filteredModels"
                valueKey="name"
                label="form.label.model"
                placeholder="form.placeholder.model"
                :error="form.errors.model"
            />

            <!-- Gateway -->
            <InputSelectWithLabel
                v-model="form.gateway_id"
                :items="props.gateways"
                itemKey="ip_address"
                label="form.label.gateway"
                placeholder="form.placeholder.gateway"
                :error="form.errors.gateway_id"
            />

            <!-- Room -->
            <InputSelectWithLabel
                v-model="form.room_id"
                :items="props.rooms.map((r) => ({ id: r.id, name: r.name }))"
                label="form.label.room_name"
                placeholder="form.placeholder.room"
                :error="form.errors.room_id"
            />

            <!-- Room Area -->
            <InputSelectWithLabel
                v-model="form.room_area_id"
                :items="filteredRoomAreas.map((ra) => ({ id: ra.id, name: ra.name }))"
                label="form.label.room_area"
                placeholder="form.placeholder.select_room_area"
                :error="form.errors.room_area_id"
            />
        </div>
    </ModalForm>
</template>
