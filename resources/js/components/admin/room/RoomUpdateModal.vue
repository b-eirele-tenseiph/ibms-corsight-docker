<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import type { Floor, RoomForm } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { DeleteFloorModal, RoomAreaInput, RoomExternalApps, RoomFloorSelector } from '.';

// Props passed into the modal component
const props = defineProps<{
    show: boolean;
    external_apps: string[];
    floors: Floor[];
    room: RoomForm;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

// Form setup initialized with values from the selected room
const form = useForm<RoomForm>({
    id: props.room.id,
    name: props.room.name,
    number: props.room.number,
    floor_id: props.room.floor_id,
    floor_name: props.room.floor_name,
    room_area: props.room.room_areas,
    external_apps: [...(props.room.external_apps ?? [])],
});

// Computes current floor name, even if the room doesn't directly contain it
const resolvedFloorName = computed(() => {
    return props.room.floor?.name ?? props.floors.find((f) => f.id === props.room.floor_id)?.name ?? '';
});

// Updates the form when the room prop changes (for reactivity when switching rooms)
watch(
    () => props.room,
    (newRoom) => {
        form.defaults({ ...newRoom, floor_id: newRoom.floor?.id, floor_name: newRoom.floor?.name });
        form.reset();
    },
    { immediate: true },
);

// Computed flag to disable the submit button if no changes made
const isBlocked = computed(() => !form.isDirty);

// State for delete floor confirmation modal
const isDeleteFloorModalOpen = ref(false);
const selectedFloorToDelete = ref<Floor | null>(null);

// Handles form submission to update the room
function handleSubmit() {
    if (!form.isDirty) return;
    form.put(`/admin/management/rooms/${form.id}`, {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
}

// Opens the delete floor modal and sets the selected floor
function openDeleteFloorModal(floor: Floor) {
    selectedFloorToDelete.value = floor;
    isDeleteFloorModalOpen.value = true;
}
</script>
<template>
    <ModalForm
        :open="show"
        title="modal.action.update_room"
        confirmLabel="action.update"
        icon="lucide:door-closed"
        :closeOnOverlayClick="true"
        variant="default"
        @close="emit('close')"
        :isBlocked="isBlocked"
        @submit="handleSubmit"
    >
        <form @submit.prevent="handleSubmit" class="max-h-full space-y-4">
            <!-- Room Name -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    v-model="form.name"
                    label="form.label.room_name"
                    placeholder="form.placeholder.room_name"
                    :error="form.errors.name"
                    required
                />
            </div>
            <!-- Room Number -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    v-model="form.number"
                    label="form.label.room_number"
                    placeholder="form.placeholder.room_number"
                    :error="form.errors.number"
                    required
                />
            </div>
            <!-- Floor Selector -->
            <RoomFloorSelector
                :floors="props.floors"
                :isUpdate="true"
                :selected="resolvedFloorName"
                @selectFloor="
                    ({ name, id }) => {
                        form.floor_name = name;
                        form.floor_id = id;
                    }
                "
                @deleteFloor="openDeleteFloorModal"
            />
            <!-- Room Area Selection -->
            <RoomAreaInput v-model="form.room_area" />

            <!-- External Applications -->
            <RoomExternalApps :apps="external_apps" v-model="form.external_apps" />
        </form>
    </ModalForm>

    <DeleteFloorModal
        :open="isDeleteFloorModalOpen"
        :floor-id="selectedFloorToDelete?.id"
        :floor-name="selectedFloorToDelete?.name"
        :used="selectedFloorToDelete?.used"
        @update:open="
            (val) => {
                isDeleteFloorModalOpen = val;
                if (!val) selectedFloorToDelete = null;
            }
        "
    />
</template>
