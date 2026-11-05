<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { SceneCommandPayload } from '@/types';
import { Slider } from '@/components/ui/slider';
import { command as deviceCommand } from '@/routes/switch_bot_devices';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    blind: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const isSceneMode = computed(() => !!props.form);
const processing = ref(false);

const tiltPosition = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.blind.id && c.type === 'setPosition');
        if (cmd) {
            const parts = cmd.command?.parameter?.split(';');
            return parts ? Number(parts[parts.length - 1]) : 0;
        }
    }
    return props.blind.latest_data?.slidePosition ?? 0;
});

const sliderValue = ref([tiltPosition.value]);

watch(
    tiltPosition,
    (p) => {
        if (!processing.value) sliderValue.value = [p];
    },
    { immediate: true },
);

const send = async (payload: { command: string; parameter?: string }, onFail?: () => void) => {
    if (processing.value) return;
    processing.value = true;

    if (isSceneMode.value) {
        emit('send-command', { id: props.blind?.id, command: payload, type: payload.command }, (ok: boolean) => {
            if (!ok) onFail?.();
            processing.value = false;
        });
    } else {
        try {
            await axios.post(deviceCommand({ device: props.blind?.id }).url, {
                commandType: 'command',
                ...payload,
            });
        } catch {
            onFail?.();
        } finally {
            processing.value = false;
        }
    }
};

const toggleStatus = (val: string) => send({ command: val });

const setBlindTilt = (pos: number) => {
    const evenPos = Math.round(pos / 2) * 2;
    sliderValue.value = [evenPos];
    send({ command: 'setPosition', parameter: `${evenPos}` }, () => {
        sliderValue.value = [tiltPosition.value];
    });
};

const isSceneActive = computed(() => isSceneMode.value && (props.form?.commands ?? []).some((c: any) => c.id === props.blind.id));
</script>
<template>
    <Card
        :class="[
            'xs:p-4 bg-controls-default h-full p-3',
            isSceneMode ? (isSceneActive ? 'bg-controls-default opacity-100' : 'bg-controls-active opacity-50') : '',
        ]"
    >
        <CardContent class="flex h-full flex-col justify-between gap-2 p-0">
            <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">{{ props.blind.name }}</Label>
            <div class="flex flex-col items-center gap-2">
                <Icon icon="material-symbols:blinds" class="size-8 md:size-10" />
                <Slider v-model="sliderValue" :max="100" :step="2" :disabled="processing" @value-commit="(val: number[]) => setBlindTilt(val[0])" />
            </div>
            <div class="flex flex-row justify-center gap-2">
                <Button variant="outline" class="xs:size-8 size-7" :disabled="processing" @click="toggleStatus('closeUp')">
                    <Icon icon="tabler:caret-up" />
                </Button>
                <Button variant="outline" class="xs:size-8 size-7" :disabled="processing" @click="toggleStatus('fullyOpen')">
                    <Icon icon="ix:line-solid" />
                </Button>
                <Button variant="outline" class="xs:size-8 size-7" :disabled="processing" @click="toggleStatus('closeDown')">
                    <Icon icon="tabler:caret-down" />
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
