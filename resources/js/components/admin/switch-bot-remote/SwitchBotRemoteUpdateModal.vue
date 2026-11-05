<script setup lang="ts">
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { update } from '@/routes/admin/management/switchbot/remotes';
import type { RemoteRoom, SwitchBotRemoteData } from '@/types/t-switch-bot-remote';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    remote: SwitchBotRemoteData;
    rooms: Array<RemoteRoom>;
}>();

const emit = defineEmits(['close']);

// Form state
const form = useForm<Pick<SwitchBotRemoteData, 'id' | 'name'>>({
    id: props.remote.id,
    name: props.remote.name,
});

// Watch `remote` changes
watch(
    () => props.remote,
    (newRemote) => {
        form.defaults({
            id: newRemote.id,
            name: newRemote.name,
        });

        form.reset();
    },
    { immediate: true },
);

// Disable submit if not dirty or processing
const isBlocked = computed(() => form.processing || !form.isDirty);

// Submit update
function handleSubmit() {
    if (!form.isDirty) return;

    form.put(update({ remote: props.remote.id }).url, {
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
        title="modal.action.update_switch_bot_remote"
        icon="fluent:iot-16-regular"
        confirmLabel="action.update"
        variant="default"
        :closeOnOverlayClick="true"
        :isBlocked="isBlocked"
    >
        <form @submit.prevent="handleSubmit" class="max-h-full space-y-4">
            <!-- Room Name (read-only) -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.room_name"
                    placeholder="form.label.room_name"
                    :model-value="props.remote.room?.name ?? '—'"
                    disabled
                />
            </div>

            <!-- Room Area Name (from prop, read-only) -->
            <div class="grid gap-1.5">
                <InputWithLabel
                    label="form.label.room_area"
                    placeholder="form.placeholder.room_area"
                    :model-value="props.remote.room_area?.name ?? '—'"
                    disabled
                />
            </div>

            <!-- Remote Name -->
            <div class="grid gap-1.5">
                <InputWithLabel label="form.label.device_name" placeholder="form.label.device_name" v-model="form.name" :error="form.errors.name" />
            </div>
        </form>
    </ModalForm>
</template>
