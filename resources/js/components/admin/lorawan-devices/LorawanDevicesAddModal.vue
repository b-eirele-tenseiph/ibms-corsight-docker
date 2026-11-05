<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { store } from '@/routes/admin/management/lorawan/devices';
import { Floor, Gateway, Manufacturer, Room, RoomArea } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    manufacturers: Array<Manufacturer>;
    gateways: Array<Gateway>;
    rooms: Array<Room>;
    roomAreas: Array<RoomArea>;
    floors: Array<Floor>;
}>();

// Emits
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();

// Form
const form = useForm({
    name: '',
    dev_eui: '',
    manufacturer_id: null,
    model: null,
    gateway_id: null,
    room_id: null,
    room_area_id: null,
    floor_id: null,
    online_flag: true,
});

// Computed
const selectedManufacturer = computed(() => props.manufacturers.find((m) => m.id === form.manufacturer_id));
const filteredModels = computed(() => selectedManufacturer.value?.models ?? []);

watch(
    () => form.manufacturer_id,
    () => {
        form.model = null;
    },
);

const filteredRooms = computed(() => {
    if (!form.floor_id) return props.rooms;
    return props.rooms.filter((room) => Number(room.floor_id) === Number(form.floor_id));
});
watch(filteredRooms, (rooms) => {
    if (!rooms.find((r) => r.id === form.room_id)) {
        form.room_id = null;
    }
});

const filteredRoomAreas = computed(() => {
    if (!form.room_id) return [];
    return props.roomAreas.filter((area) => area.room_id === form.room_id);
});
watch(
    () => form.room_id,
    () => {
        form.room_area_id = null;
    },
);

// Cancel
function onCancel() {
    form.reset();
    form.clearErrors();
    emit('close');
}

// Submit
function onSubmit() {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            emit('submit');
            emit('close');
        },
    });
}
</script>

<template>
    <ModalForm
        :open="props.isOpen"
        title="modal.action.add_lorawan_devices"
        :confirmLabel="'action.create'"
        :isProcessing="form.processing"
        @close="onCancel"
        @submit="onSubmit"
        :isBlocked="form.processing || !form.isDirty"
        icon="tabler:bulb"
        :iconStyle="'text-primary-text h-6 w-6'"
        variant="default"
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
                :items="props.manufacturers"
                v-model="form.manufacturer_id"
                label="form.label.manufacturer"
                placeholder="form.placeholder.manufacturer"
                :error="form.errors.manufacturer_id"
            />

            <!-- Model -->
            <InputSelectWithLabel
                v-if="filteredModels.length"
                :items="filteredModels"
                valueKey="name"
                v-model="form.model"
                label="form.label.model"
                placeholder="form.placeholder.model"
                :error="form.errors.model"
            />

            <!-- Gateway -->
            <InputSelectWithLabel
                :items="props.gateways"
                itemKey="ip_address"
                v-model="form.gateway_id"
                label="form.label.gateway"
                placeholder="form.placeholder.gateway"
                :error="form.errors.gateway_id"
            />

            <!-- Room -->
            <InputSelectWithLabel
                :items="filteredRooms"
                v-model="form.room_id"
                label="form.label.room_name"
                placeholder="form.placeholder.room"
                :error="form.errors.room_id"
            />

            <!-- Room Area -->
            <InputSelectWithLabel
                :items="filteredRoomAreas"
                v-model="form.room_area_id"
                label="form.label.room_area"
                placeholder="form.placeholder.select_room_area"
                :error="form.errors.room_area_id"
                :disabled="!form.room_id"
            />
        </div>
    </ModalForm>
</template>
