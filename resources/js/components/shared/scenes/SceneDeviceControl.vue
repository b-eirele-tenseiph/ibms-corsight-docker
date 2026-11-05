<!-- eslint-disable vue/no-mutating-props -->
<script setup lang="ts">
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { Device, SceneCommandPayload } from '@/types';
import type { Component } from 'vue';
import { computed, inject } from 'vue';
import { AirconCard, BlindTiltCard, ControlCard, CurtainCard, ProjectorCard, SwitchCard, TelevisionCard, ThermostatCard } from '../device-controls';

const componentMap: Record<string, { component: Component; prop: string; class: string }> = {
    AirconCard: { component: AirconCard, prop: 'aircon', class: 'sm:min-w-112' },
    BlindTiltCard: { component: BlindTiltCard, prop: 'blind', class: 'min-w-55' },
    ControlCard: { component: ControlCard, prop: 'appliance', class: 'min-w-36' },
    CurtainCard: { component: CurtainCard, prop: 'curtain', class: 'min-w-55' },
    ProjectorCard: { component: ProjectorCard, prop: 'projector', class: 'min-w-55 sm:min-w-80' },
    SwitchCard: { component: SwitchCard, prop: 'light', class: 'sm:min-w-36' },
    TelevisionCard: { component: TelevisionCard, prop: 'television', class: 'min-w-55 sm:min-w-80' },
    ThermostatCard: { component: ThermostatCard, prop: 'thermostat', class: 'sm:min-w-96' },
};

const props = defineProps<{
    open: boolean;
    item: Device | Device[];
    form: any;
}>();
const emit = defineEmits(['close']);

const activeDevice = computed(() => (props.item && !Array.isArray(props.item) ? props.item : null));

const activeComponent = computed(() => (activeDevice.value ? (componentMap[activeDevice.value.component] ?? null) : null));

const resolveDeviceId = (d: any): string => String(d.device_id ?? d.id ?? '');

const onSendCommand = (payload: SceneCommandPayload, done?: (ok: boolean) => void) => {
    try {
        if (!props.form.commands) props.form.commands = [];
        if (!props.form.devices) props.form.devices = [];

        const cmds = props.form.commands;
        const devs = props.form.devices;

        if (payload.remove) {
            // Remove matching commands in-place
            const indicesToRemove = cmds
                .map((c: any, i: number) => (c.id === payload.id && c.type === payload.type ? i : -1))
                .filter((i: number) => i >= 0)
                .reverse();
            indicesToRemove.forEach((i: number) => cmds.splice(i, 1));

            const deviceIndex = devs.findIndex((d: any) => resolveDeviceId(d) === String(payload.id));
            if (deviceIndex !== -1) {
                devs.splice(deviceIndex, 1);
            }

            // Re-add device if commands remain
            cmds.filter((c: any) => c.id === payload.id).forEach((c: any) => {
                devs.push({
                    device_id: payload.id,
                    name: activeDevice.value?.name,
                    command: c.command,
                    command_type: c.type,
                });
            });

            done?.(true);
            return;
        }

        const entry = { id: payload.id, type: payload.type, command: payload.command };

        const i = cmds.findIndex((c: any) => c.id === entry.id && c.type === entry.type);
        if (i === -1) {
            cmds.push(entry);
        } else {
            cmds[i] = entry;
        }

        const deviceInDevs = devs.find((d: any) => resolveDeviceId(d) === String(payload.id));
        if (!deviceInDevs) {
            devs.push({
                device_id: payload.id,
                name: activeDevice.value?.name,
                command: payload.command,
                command_type: payload.type,
            });
        } else {
            deviceInDevs.command = payload.command;
            deviceInDevs.command_type = payload.type;
        }

        done?.(true);
    } catch {
        done?.(false);
    }
};
</script>
<template>
    <ModalForm
        :open="props.open"
        @close="emit('close')"
        cancelLabel="action.back"
        title="modal.action.scene_device_control"
        icon="fluent:iot-16-regular"
    >
        <div class="mb-6 flex flex-col items-center gap-6 text-sm">
            <div :class="activeComponent?.class ?? ''">
                <Component
                    v-if="activeComponent"
                    :is="activeComponent.component"
                    v-bind="{ [activeComponent.prop]: activeDevice }"
                    :form="props.form"
                    :icon="activeDevice?.icon ? undefined : inject('icon')"
                    @send-command="onSendCommand"
                    class="mt-4 rounded-lg border p-4"
                />
            </div>
        </div>
    </ModalForm>
</template>
