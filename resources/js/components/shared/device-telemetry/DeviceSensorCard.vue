<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps<{
    sensor: Record<string, any>;
    icon?: any;
}>();

const devModel = computed(() => props.sensor.model?.toUpperCase());
const latestVal = computed<boolean>(() => props.sensor.latest_data?.status.value);

const isOn = computed<boolean>(() => [true, 'true', 1, '1'].includes(latestVal.value as any));

const sensorIcons = computed(() => props.icon || {});

const status = computed(() => {
    const model = devModel.value || '';
    if (!model) return isOn.value ? 'active' : 'inactive';

    if (model.includes('WS202')) return isOn.value ? 'motion_detected' : 'no_motion';
    if (model.includes('R313A')) return isOn.value ? 'closed' : 'open';
    if (model.includes('RB11E')) return isOn.value ? 'occupied' : 'vacant';
    return isOn.value ? 'active' : 'inactive';
});

const sensorIcon = computed(() => {
    return sensorIcons.value[devModel.value]?.[status.value] || 'material-symbols:sensors';
});

const sensorColor = computed(() => {
    const m = devModel.value || '';
    return m.includes('WS202') || m.includes('RB11E') ? (isOn.value ? 'text-red-500' : 'text-green-500') : 'text-primary-text';
});
</script>

<template>
    <Card :class="['xs:p-4 h-full p-3', latestVal ? 'bg-controls-active' : 'bg-controls-default']">
        <CardContent class="flex h-full flex-col justify-between gap-2 p-0">
            <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">
                {{ sensor.room_area?.name || sensor.room.name }}
            </Label>
            <div :class="['flex h-full flex-col items-center justify-center gap-4', sensorColor]">
                <Icon :icon="sensorIcon" class="size-8 md:size-10" />
                <span class="fluid-text-xs-sm w-full truncate text-center font-semibold">
                    {{ $t(`device.telemetry.${status}`) }}
                </span>
            </div>
        </CardContent>
    </Card>
</template>
