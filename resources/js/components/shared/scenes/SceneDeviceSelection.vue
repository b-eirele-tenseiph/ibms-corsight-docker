<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { Button } from '@/components/ui/button';
import { useModal } from '@/composables/useModal';
import { Device } from '@/types';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';
import { SceneDeviceControl } from '.';
import { SpinnerIcon } from '../icons/spinner-icon';

const props = defineProps<{
    open: boolean;
    items: Device[];
    form: any;
    isLoading: boolean;
}>();

const emit = defineEmits(['close']);

const modals = { device: useModal<any>() };

const {
    device: { visible: showDeviceModal, data: deviceData, close: closeModal },
} = modals;

const groupedDevices = computed(() => {
    return props.items.reduce(
        (acc, d) => {
            const area = d.room_area?.name || 'Unassigned';
            (acc[area] ??= []).push(d);
            return acc;
        },
        {} as Record<string, Device[]>,
    );
});

const selectedIds = computed(() => new Set((props.form.commands ?? []).map((c: { id: string; device_id?: string }) => c.id || c.device_id)));
</script>
<template>
    <ModalForm
        :open="props.open"
        @close="emit('close')"
        cancelLabel="action.back"
        title="modal.action.scene_device_selection"
        icon="fluent:iot-16-regular"
    >
        <div v-if="isLoading" class="my-6 flex h-full items-center justify-center">
            <SpinnerIcon />
        </div>
        <div v-else-if="!form.room_id" class="flex flex-col gap-6 text-sm">{{ $t('modal.alert.no_room_selected') }}</div>
        <div v-else-if="items.length === 0" class="flex flex-col gap-6 text-sm">{{ $t('modal.alert.no_devices_in_room') }}</div>
        <div v-else class="flex flex-col gap-6 text-sm">
            <div v-for="(devices, areaName) in groupedDevices" :key="areaName" class="flex flex-col gap-4">
                <label class="font-bold">{{ areaName }} {{ $t('page.scene_management.label.area') }}:</label>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Button
                        v-for="device in devices"
                        :key="device.id"
                        class="h-auto w-full justify-start text-left"
                        :variant="selectedIds.has(device.id) ? 'default' : 'secondary'"
                        @click="modals.device.open(device)"
                    >
                        <Icon :icon="device.icon" />
                        {{ device.name }}
                    </Button>
                </div>
            </div>
        </div>
    </ModalForm>

    <SceneDeviceControl :item="deviceData || []" :open="showDeviceModal" @close="closeModal" :form="props.form" />
</template>
