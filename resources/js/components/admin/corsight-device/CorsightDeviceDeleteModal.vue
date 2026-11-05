<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { destroy } from '@/routes/admin/management/corsight/devices';
import { CorsightDeviceData } from '@/types';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    show: boolean;
    device?: Pick<CorsightDeviceData, 'id' | 'name'>;
}>();

// Emits events to parent component (used for closing modal)
const emit = defineEmits(['close']);

const deleteCorsightDevice = reactive({
    onSubmit: () => {
        router.delete(destroy({ device: props.device!.id }).url, {
            onSuccess: () => {
                emit('close');
            },
        });
    },
}) as any;
</script>
<template>
    <ModalForm
        :open="props.show"
        @close="emit('close')"
        @submit="deleteCorsightDevice.onSubmit"
        title="modal.action.delete_corsight_device"
        confirmLabel="action.delete"
        icon="lucide:scan-face"
        variant="destructive"
    >
        <div class="flex flex-col gap-4 text-sm">
            <p class="normal overflow-hidden font-semibold">{{ $t('modal.confirm.delete_corsight_device') }}</p>
            <p class="text-destructive font-semibold">{{ $t('modal.confirm.warning') }}</p>
            <div class="flex flex-col gap-2">
                <label
                    ><span class="font-semibold">{{ $t('form.label.device_name') }}:</span> {{ device?.name }}</label
                >
            </div>
        </div>
    </ModalForm>
</template>
