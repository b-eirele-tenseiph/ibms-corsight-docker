<script setup lang="ts">
import { Icon } from '@iconify/vue';

const props = defineProps<{
    status: number | null | undefined;
    type: string;
}>();

const supportedBatteryTypes = ['Curtain', 'Bot', 'Smart Lock Pro', 'Smart Lock', 'Blind Tilt', 'WoIOSensor'] as const;

// Choose icon name based on battery level
function getBatteryIcon(status: number | null | undefined) {
    if (status === null || status === undefined) return 'ix:battery-xmark';
    if (status > 59) return 'ix:battery-full';
    if (status > 19) return 'ix:battery-half';
    if (status > 9) return 'ix:battery-quarter';
    if (status > 0) return 'ix:battery-low';
    return 'ix:battery-empty';
}

const isBatteryDevice = supportedBatteryTypes.includes(props.type as (typeof supportedBatteryTypes)[number]);

// Icon color (optional, can be Tailwind or inline)
const batteryColor = (status: number | null | undefined) => {
    if (status === null || status === undefined) return '#818181';
    if (status > 59) return '#4CAF50';
    if (status > 19) return '#8BC34A';
    if (status > 9) return '#FFEB3B';
    if (status > 0) return '#F44336';
    return '#D32F2F';
};
</script>

<template>
    <!-- Battery Device Icons -->
    <Icon v-if="isBatteryDevice" :icon="getBatteryIcon(props.status)" :style="{ color: batteryColor(props.status) }" class="size-7" />

    <!-- For Keypad Touch -->
    <Icon v-else-if="props.type === 'Keypad Touch'" icon="ix:battery-slash" class="size-7 text-[#818181]" />

    <!-- Fallback icon -->
    <Icon v-else icon="ph:plug-bold" class="size-6 text-[#818181]" />
</template>
