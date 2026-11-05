<script setup lang="ts">
import { Switch } from '@/components/ui/switch';
import { command as deviceCommand } from '@/routes/switch_bot_devices';
import { SwitchBotDeviceData } from '@/types';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    applianceItem: SwitchBotDeviceData;
}>();

const processing = ref(false);

const isOn = computed(() => {
    const raw = props.applianceItem?.latest_data?.power ?? props.applianceItem?.latest_data?.powerState;
    return String(raw).toUpperCase() === 'ON';
});

const pendingState = ref<boolean | null>(null);

watch(isOn, (confirmed) => {
    if (pendingState.value !== null && confirmed === pendingState.value) {
        pendingState.value = null;
    }
});

const displayedOn = computed(() => pendingState.value ?? isOn.value);

const handleToggle = (newState: boolean) => {
    // Prevent duplicate in-flight commands.
    if (processing.value) return;

    processing.value = true;
    pendingState.value = newState;

    axios
        .post(deviceCommand({ device: props.applianceItem.id }).url, {
            command: newState ? 'turnOn' : 'turnOff',
        })
        .catch(() => {
            pendingState.value = null;
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <div class="flex items-center justify-start">
        <Switch v-if="applianceItem?.latest_data" :model-value="displayedOn" :disabled="processing" @update:model-value="handleToggle" />
    </div>
</template>
