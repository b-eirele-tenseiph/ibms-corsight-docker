<script setup lang="ts">
import { InputSelectWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/admin/management/switchbot/hubs';
import { HubRoom, SwitchBotHubData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    hubs: SwitchBotHubData;
    rooms: Array<HubRoom>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const form = useForm<Pick<SwitchBotHubData, 'id' | 'name' | 'room_area_id'>>({
    id: props.hubs.id,
    name: props.hubs.name,
    room_area_id: props.hubs?.room_area?.id,
});

// Computed flag to disable the submit button if no changes made
const isBlocked = computed(() => form.processing || !form.isDirty);

// Get the current room based on the device's room_area.room_id
const currentRoom = computed(() => props.rooms.find((r) => r.id === props.hubs.room?.id));

// Room areas for the current room only, formatted for the select component
const roomAreaItems = computed(() => {
    if (!currentRoom.value?.room_areas) return [];
    return currentRoom.value.room_areas.map((area: any) => ({ ...area }));
});

const submitEditUser = () => {
    form.put(update(props.hubs.id).url, {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};
</script>

<template>
    <ModalForm
        :open="show"
        @submit="submitEditUser"
        @close="emit('close')"
        title="modal.action.update_switch_bot_hub"
        icon="lucide:smartphone"
        confirmLabel="action.update"
        variant="default"
        :closeOnOverlayClick="true"
        :isBlocked="isBlocked"
    >
        <form @submit.prevent="submitEditUser" class="flex flex-col gap-y-4">
            <!-- ROOM NAME -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.room_name"
                    placeholder="form.label.room_name"
                    :model-value="currentRoom?.name || ''"
                    :disabled="true"
                />
            </div>

            <!-- ROOM AREA -->
            <div class="grid gap-1.5">
                <InputSelectWithLabel
                    label="form.label.room_area"
                    placeholder="form.placeholder.room_area"
                    v-model="form.room_area_id"
                    :items="roomAreaItems"
                    :error="form.errors.room_area_id"
                />
            </div>

            <!-- HUB NAME -->
            <div class="flex flex-col gap-y-4">
                <InputWithLabel
                    label="form.label.hub_name"
                    type="text"
                    placeholder="form.placeholder.hub_name"
                    v-model:modelValue="form.name"
                    :error="form.errors.name"
                />
            </div>
        </form>
    </ModalForm>
</template>
