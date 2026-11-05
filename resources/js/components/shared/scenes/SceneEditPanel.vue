<script setup lang="ts">
import { useRoomDevices } from '@/composables/useRoomDevices';
import { update as updateScene } from '@/routes/scenes';
import { Room } from '@/types/t-room';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import { computed, watch } from 'vue';
import { SceneForm } from '.';

dayjs.extend(timezone);
dayjs.extend(utc);
dayjs.extend(customParseFormat);

const props = defineProps<{
    admin?: boolean;
    scene: any;
    rooms: Room[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const ALL_DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as const;

const isEveryday = (days: string[]) => {
    return ALL_DAYS.every((day) => days.includes(day));
};

const parsedDays = props.scene?.days ? JSON.parse(props.scene.days) : [];

const originalSceneDevices = props.scene?.scene_devices ?? [];

const originalCommands = Array.isArray(originalSceneDevices)
    ? originalSceneDevices.map((device: any) => ({
          id: device.device_id,
          command: device.command,
          type: device.command_type,
      }))
    : [];

const form = useForm({
    id: props.scene?.id ?? null,
    name: props.scene?.name ?? '',
    room_id: props.scene?.room_id ?? '',
    color: props.scene?.context?.palette ?? 'blue',
    icon: props.scene?.context?.icon ?? 'tabler:bulb',
    scheduled: props.scene?.scheduled ?? false,
    time: props.scene?.time ? dayjs.utc(props.scene.time, 'HH:mm').tz(dayjs.tz.guess()).format('HH:mm') : '',
    scheduleType: props.scene?.scheduled ? (isEveryday(parsedDays) ? 'everyday' : 'custom') : 'everyday',
    days: parsedDays,
    devices: props.scene?.scene_devices ?? [],
    commands: Array.isArray(props.scene?.scene_devices)
        ? props.scene.scene_devices.map((device: any) => ({
              id: device.device_id,
              command: device.command,
              type: device.command_type,
          }))
        : [],
});

const { devices, isLoading } = useRoomDevices(computed(() => form.room_id));

watch(
    () => form.room_id,
    (newRoomId) => {
        if (newRoomId === props.scene?.room_id) {
            form.devices = originalSceneDevices;
            form.commands = originalCommands;
        } else {
            form.devices = [];
            form.commands = [];
        }
    },
);

watch(
    () => form.scheduled,
    (scheduled) => {
        if (scheduled) {
            const days = props.scene?.days ? JSON.parse(props.scene.days) : [];
            form.time = props.scene?.time ? dayjs.utc(props.scene.time, 'HH:mm').tz(dayjs.tz.guess()).format('HH:mm') : '';
            form.days = days;
            form.scheduleType = isEveryday(days) ? 'everyday' : 'custom';
        } else {
            form.time = '';
            form.days = [];
            form.scheduleType = 'everyday';
        }
    },
);

const update = () => {
    form.transform((data) => ({
        ...data,
        days: data.scheduled ? (data.scheduleType === 'everyday' ? [...ALL_DAYS] : (data.days ?? [])) : [],
    })).put(updateScene({ scene: props.scene }).url, {
        onSuccess: () => {
            (form as any).reset();
            emit('close');
        },
    });
};
</script>

<template>
    <SceneForm :isLoading :items="devices" :scene :rooms :form="form" mode="edit" @submit="update" :admin />
</template>
