<script setup lang="ts">
import { useRoomDevices } from '@/composables/useRoomDevices';
import { store } from '@/routes/scenes';
import { Room } from '@/types/t-room';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { SceneForm } from '.';

const props = defineProps<{
    admin?: boolean;
    rooms: Room[] | Room;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const ALL_DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as const;

const form = useForm({
    name: '',
    room_id: props.admin ? '' : String((props.rooms as Room).id ?? ''),
    color: '',
    icon: '',
    scheduled: false,
    time: '',
    scheduleType: 'everyday',
    days: [],
    devices: [],
    commands: [],
});

const { devices, isLoading } = useRoomDevices(computed(() => form.room_id));

watch(
    () => form.room_id,
    () => {
        form.devices = [];
        form.commands = [];
    },
);

watch(
    () => form.scheduled,
    (scheduled) => {
        if (!scheduled) {
            form.time = '';
            form.days = [];
            form.scheduleType = 'everyday';
        }
    },
);

const create = () => {
    form.transform((data) => ({
        ...data,
        days: data.scheduled ? (data.scheduleType === 'everyday' ? [...ALL_DAYS] : (data.days ?? [])) : [],
    })).post(store.url(), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <SceneForm :isLoading :key="form.room_id" :items="devices" :rooms :form="form" mode="create" @submit="create" :admin />
</template>
