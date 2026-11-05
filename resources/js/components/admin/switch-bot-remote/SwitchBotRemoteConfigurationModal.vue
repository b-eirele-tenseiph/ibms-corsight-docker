<script setup lang="ts">
import { AlertBox } from '@/components/shared/alert-box';
import { ModalForm } from '@/components/shared/modals';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { settings } from '@/routes/admin/management/switchbot/remotes';
import { SwitchBotRemoteConfigData, SwitchBotRemoteData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    device: SwitchBotRemoteData;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const form = useForm<SwitchBotRemoteConfigData>({
    enable_data_storage: false,
});

watch(
    () => props.device,
    (device) => {
        if (device) {
            form.defaults({ enable_data_storage: device.enable_data_storage });
            form.reset();
        }
    },
    { immediate: true },
);

const onCancel = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const onSubmit = () => {
    form.put(settings(props.device?.id).url, {
        onSuccess: () => onCancel(),
    });
};
</script>

<template>
    <ModalForm
        :open="isOpen"
        :isProcessing="form.processing"
        title="Device Configuration"
        confirmLabel="Save"
        icon="pepicons-pencil:gear"
        :isBlocked="form.processing || !form.isDirty"
        @close="onCancel"
        @submit="onSubmit"
    >
        <div class="max-h-[60vh] pr-2 [&>*:not(:nth-last-child(2))]:mb-4">
            <div class="space-y-2">
                <Label class="fluid-text-xs-sm">{{ $t('form.label.device_information') }}</Label>
                <AlertBox type="info" :message="$t('modal.alert.data_store_info')" />
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <Label class="fluid-text-xs-sm font-medium">{{ $t('form.label.enable_storage') }}</Label>
                    <Switch v-model="form.enable_data_storage" />
                </div>
            </div>
        </div>
    </ModalForm>
</template>
