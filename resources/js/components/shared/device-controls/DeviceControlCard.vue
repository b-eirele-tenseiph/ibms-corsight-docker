<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { SceneCommandPayload } from '@/types';
import { Switch } from '@/components/ui/switch';
import { command as deviceCommand } from '@/routes/switch_bot_devices';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, inject, ref } from 'vue';

const props = defineProps<{
    appliance: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const isSceneMode = computed(() => !!props.form);

const processing = ref(false);

const localOverride = ref<boolean | null>(null);

const deviceIcon = inject<Record<string, string>>('icon', {});

const dataEnabled = computed(() =>
    Object.entries({
        powerState: 'ON',
        lockState: 'LOCKED',
        power: 'ON',
    }).some(([key, expected]) => props.appliance.latest_data?.[key]?.toUpperCase() === expected),
);

const localEnabled = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.appliance.id);
        if (cmd) {
            return ['turnOn', 'lock'].includes(cmd.command?.command);
        }
    }

    if (localOverride.value !== null) return localOverride.value;
    return dataEnabled.value;
});

const isLock = computed(
    () =>
        ['Lock', 'Smart Lock', 'Smart Lock Pro'].includes(props.appliance?.name) ||
        ['Lock', 'Smart Lock', 'Smart Lock Pro'].includes(props.appliance?.type?.name),
);

const toggleStatus = async (newValue: boolean) => {
    if (processing.value) return;

    processing.value = true;

    const cmd = newValue ? (isLock.value ? 'lock' : 'turnOn') : isLock.value ? 'unlock' : 'turnOff';

    if (isSceneMode.value) {
        emit(
            'send-command',
            {
                id: props.appliance?.id,
                command: { command: cmd },
                type: 'status',
            },
            () => {
                processing.value = false;
            },
        );

        return;
    }

    localOverride.value = newValue;

    try {
        await axios.post(
            deviceCommand({
                device: props.appliance?.id,
            }).url,
            {
                command: cmd,
            },
        );
    } catch {
        localOverride.value = dataEnabled.value;
    } finally {
        processing.value = false;
    }
};

const icon = computed(() => {
    const name = props.appliance.name.toLowerCase();
    const type = (props.appliance.type ?? '').toString().toLowerCase();

    if (name.includes('lock') || type.includes('lock')) {
        return localEnabled.value ? 'material-symbols:lock-outline' : 'material-symbols:lock-open-outline';
    }

    if (props.appliance.icon && typeof props.appliance.icon === 'string') {
        return props.appliance.icon;
    }

    const iconMap: Record<string, string> = {
        ...deviceIcon,
    };

    const matchKey = Object.keys(iconMap).find((key) => type.includes(key) || name.includes(key));

    return matchKey ? iconMap[matchKey] : 'material-symbols:outlet-outline';
});

const isSceneActive = computed(() => isSceneMode.value && (props.form?.commands ?? []).some((c: any) => c.id === props.appliance.id));

const gridSetting = inject<{ column: number } | null>('gridSetting', null);

const switchSize = computed(() => {
    const column = gridSetting?.column ?? 0;
    return [2, 3, 4].includes(column) ? 'lg' : '2xl';
});
</script>

<template>
    <Card
        :class="[
            'xs:p-4 h-full p-3',
            localEnabled ? 'bg-controls-active' : 'bg-controls-default',
            isSceneMode ? (isSceneActive ? 'bg-controls-default opacity-100' : 'bg-controls-active opacity-50') : '',
        ]"
    >
        <CardContent class="flex h-full flex-col justify-between gap-2 p-0">
            <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">
                {{ props.appliance.name }}
            </Label>
            <div class="flex h-full flex-col items-center justify-center gap-4">
                <Icon :icon="icon" class="size-8 md:size-10" />
                <Switch
                    :model-value="localEnabled"
                    :rootSize="switchSize"
                    :thumbSize="switchSize"
                    :disabled="processing"
                    @update:model-value="toggleStatus"
                />
            </div>
        </CardContent>
    </Card>
</template>
