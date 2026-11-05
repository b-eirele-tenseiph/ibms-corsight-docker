<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { SceneCommandPayload } from '@/types';
import { Switch } from '@/components/ui/switch';
import { command } from '@/routes/lorawan_devices';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, inject, ref } from 'vue';

const props = defineProps<{
    light: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const isSceneMode = computed(() => !!props.form);

const switchData = computed(() => {
    const latestData = props.light?.latest_data || {};
    const baseData = Object.keys(latestData)
        .filter((key) => key.startsWith('switch_'))
        .map((key) => ({ type: key, context: { ...latestData[key] } }));

    if (!isSceneMode.value) return baseData;

    const commands = (props.form?.commands ?? []).filter((c: any) => c.id === props.light.id);

    return baseData.map((data) => {
        const cmd = commands.find((c: any) => c.type === data.type);

        if (!cmd) {
            return { ...data, context: { ...data.context, value: 'low' } };
        }

        const isOn = cmd.command?.command?.toLowerCase().includes('on');
        return { ...data, context: { ...data.context, value: isOn ? 'high' : 'low' } };
    });
});

const localOverrides = ref<Record<number, boolean>>({});

const switchStates = computed(() =>
    switchData.value.map((data, i) => {
        if (i in localOverrides.value) return localOverrides.value[i];
        return data.context?.value === 'high';
    }),
);

const processing = ref(false);

const toggleStatus = (index: number, val: boolean) => {
    processing.value = true;
    const data = switchData.value[index];
    const cmd = `turn${val ? 'On' : 'Off'}S${data.type.split('_')[1]}`;
    localOverrides.value[index] = val;

    const onDone = () => {
        processing.value = false;
    };

    if (isSceneMode.value) {
        emit('send-command', { id: props.light?.id, command: { command: cmd }, type: data.type }, () => {
            onDone();
        });
    } else {
        axios
            .post(command({ device: props.light?.id }).url, { command: cmd })
            .catch(() => {
                localOverrides.value[index] = !val;
            })
            .finally(() => {
                onDone();
            });
    }
};

const isAnySwitchOn = computed(() => switchStates.value.includes(true));

const gridSetting = inject<{ column: number }>('gridSetting', { column: 2 });

const switchSize = computed(() => ([2, 3, 4].includes(gridSetting?.column ?? 0) ? 'lg' : '2xl'));

const isSceneActive = computed(() => isSceneMode.value && !!props.form?.commands?.find((cmd: any) => cmd.id === props.light.id));
</script>

<template>
    <Card
        :class="[
            isAnySwitchOn ? 'bg-controls-active' : 'bg-controls-default',
            isSceneMode ? (isSceneActive ? 'bg-controls-default opacity-100' : 'bg-controls-active opacity-50') : '',
            'xs:p-4 h-full p-3',
        ]"
    >
        <CardContent class="flex h-full flex-col justify-between gap-2 p-0">
            <Label class="fluid-text-xs-sm shrink-0 truncate leading-5 font-semibold">{{ props.light.name }}</Label>
            <div class="flex h-full flex-col items-center justify-center gap-4">
                <Icon :icon="isAnySwitchOn ? 'mdi:lightbulb-on-outline' : 'mdi:lightbulb-outline'" class="size-8 md:size-10" />
                <div class="flex justify-center gap-4">
                    <div class="flex justify-center gap-4">
                        <div v-for="(data, i) in switchData" :key="data.type" class="flex w-16 flex-col items-center gap-1">
                            <Switch
                                :model-value="switchStates[i]"
                                :root-size="switchSize"
                                :thumb-size="switchSize"
                                :disabled="processing"
                                @update:model-value="(val) => toggleStatus(i, val)"
                            />
                            <span v-if="props.light.context?.[data.type]" class="text-secondary-text w-full truncate text-center text-xs">
                                {{ props.light.context[data.type] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
