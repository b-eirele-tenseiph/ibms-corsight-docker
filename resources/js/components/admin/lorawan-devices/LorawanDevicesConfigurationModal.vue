<script setup lang="ts">
import { AlertBox } from '@/components/shared/alert-box';
import { InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { settingsIndex, settingsUpdate } from '@/routes/admin/management/lorawan/devices';
import { LorawanDeviceData } from '@/types/t-lorawan-devices';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    device: LorawanDeviceData | null;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const form = useForm<Pick<LorawanDeviceData, 'enable_data_storage' | 'settings'>>({
    enable_data_storage: false,
    settings: {},
});

watch(
    () => props.device,
    async (device) => {
        if (!device) return;
        // Fetch configurable settings
        await axios
            .get(settingsIndex({ device: device.id }).url)
            .then((response) => {
                form.defaults({
                    enable_data_storage: response.data.enable_data_storage,
                    settings: response.data.settings ?? {},
                });
                form.reset();
            })
            .finally(() => {});
    },
    { immediate: true },
);

const onCancel = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const onSubmit = () => {
    form.put(settingsUpdate({ id: props.device!.id }).url, {
        onSuccess: () => onCancel(),
    });
};
</script>

<template>
    <ModalForm
        :open="props.isOpen"
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

            <template v-for="(setting, key) in form.settings" :key="key">
                <InputWithLabel
                    v-model="form.settings[key]"
                    :label="`device.lorawan.${key}`"
                    placeholder="form.placeholder.enter_device_name"
                    :error="form.errors[`settings.${key}`]"
                />
            </template>

            <!-- Error message display -->
            <div v-show="form.errors.settings">
                <p class="text-destructive text-sm">
                    {{ form.errors.settings }}
                </p>
            </div>
        </div>
    </ModalForm>
</template>
