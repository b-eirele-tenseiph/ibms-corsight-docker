<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import type { Floor, RoomForm } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { RoomAreaInput, RoomExternalApps, RoomFloorSelector } from '.';

// Props passed into the modal component
const props = defineProps<{
    show: boolean;
    external_apps: string[];
    floors: Floor[];
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

// Reactive form object for room data
const form = useForm<RoomForm>({
    id: null,
    name: '',
    number: '',
    floor_id: null,
    floor_name: '',
    room_area: [],
    external_apps: [],
});

// Resets form state and clears errors
function resetForm() {
    form.reset();
    form.clearErrors();
}

// Computed flag to disable the submit button if no changes made
const isBlocked = computed(() => !form.isDirty);

// Handles form submission to Create a room
function handleSubmit() {
    form.post('/admin/management/rooms', {
        onSuccess: () => {
            emit('close');
            resetForm();
        },
    });
}

// watch for open and close modal to clear form
watch(
    () => props.show,
    (val) => {
        if (!val) resetForm();
    },
);
</script>

<template>
    <ModalForm
        :open="props.show"
        title="modal.action.add_room"
        confirmLabel="action.create"
        icon="lucide:door-closed"
        :closeOnOverlayClick="false"
        @close="emit('close')"
        :isBlocked="isBlocked"
        @submit="handleSubmit"
        variant="default"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
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

            <!-- Floor Selection -->
            <RoomFloorSelector
                :floors="props.floors"
                :isUpdate="false"
                @selectFloor="
                    ({ name, id }) => {
                        form.floor_name = name;
                        form.floor_id = id;
                    }
                "
            />
            <!-- Room Area Selection -->
            <RoomAreaInput v-model="form.room_area" />
            <!-- External Applications -->
            <RoomExternalApps :apps="external_apps" v-model="form.external_apps" />
        </form>
    </ModalForm>
</template>
