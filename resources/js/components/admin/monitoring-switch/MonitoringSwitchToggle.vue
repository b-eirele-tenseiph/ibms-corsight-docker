<script setup lang="ts">
import { Switch } from '@/components/ui/switch';
import { command } from '@/routes/lorawan_devices';
import { LorawanSwitch } from '@/types';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps<{
    switchItem?: LorawanSwitch;
}>();

const processing = ref(false);

const latestData = computed(() =>
    Object.entries(props.switchItem?.latest_data ?? {}).map(([type, d]) => {
        const data = d as { value: 'high' | 'low' };
        return { type, value: data.value };
    }),
);

const handleToggle = (newState: boolean, data: { type: string; value: 'high' | 'low' }) => {
    processing.value = true;
    const prev = data.value;
    const src = props.switchItem!.latest_data[data.type];

    // Update original source data
    src.value = newState ? 'high' : 'low';

    axios
        .post(command({ device: props.switchItem!.id }).url, {
            command: `turn${newState ? 'On' : 'Off'}S${data.type.split('_')[1]}`,
        })
        .catch(() => setTimeout(() => (src.value = prev), 300))
        .finally(() => (processing.value = false));
};

const getAlias = (type: string) => props.switchItem?.context[type] ?? type;
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <div v-for="data in latestData" :key="data.type" class="flex items-center gap-2">
            <label>{{ getAlias(data.type) }}</label>
            <Switch :model-value="data.value === 'high'" @update:model-value="(newState) => handleToggle(newState, data)" :disabled="processing" />
        </div>
    </div>
</template>
