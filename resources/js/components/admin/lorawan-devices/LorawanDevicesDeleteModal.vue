<script setup lang="ts">
import { ModalForm } from '@/components/shared/modals';
import { destroy } from '@/routes/admin/management/lorawan/devices';
import { LorawanDeviceData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    device: LorawanDeviceData | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit'): void;
}>();

const form = useForm({});
const isProcessing = computed(() => form.processing);
function onClose() {
    form.reset();
    emit('close');
}

function onSubmit() {
    if (!props.device?.id) return;

    form.delete(destroy(props.device.id).url, {
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
        title="modal.action.delete_lorawan_device"
        :isProcessing="isProcessing"
        :confirmLabel="'action.delete'"
        @close="onClose"
        @submit="onSubmit"
        icon="heroicons-outline:exclamation"
        variant="destructive"
    >
        <p class="text-primary-text text-sm font-medium">{{ $t('modal.confirm.delete_device') }}</p>
        <p class="text-destructive mt-2 text-sm font-semibold">{{ $t('modal.confirm.delete_device_body') }}</p>

        <div class="mt-4 text-sm">
            <p>
                <span class="text-primary-text font-semibold">{{ $t('form.label.room_name') }}:</span>
                <span class="ml-1">{{ props.device?.room?.name || '—' }}</span>
            </p>
            <p class="mt-1">
                <span class="text-primary-text font-semibold">{{ $t('form.label.device_name') }}:</span>
                <span class="ml-1">{{ props.device?.name || '—' }}</span>
            </p>
        </div>
    </ModalForm>
</template>
