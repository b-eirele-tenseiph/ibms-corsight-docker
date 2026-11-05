<!-- eslint-disable vue/no-mutating-props -->
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { SceneCommandPayload } from '@/types';
import { command as remoteCommand } from '@/routes/switch_bot_remotes';
import { Icon } from '@iconify/vue';
import { useMediaQuery } from '@vueuse/core';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps<{
    projector: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const isSceneMode = computed(() => !!props.form);
const processing = ref(false);
const isSceneActive = computed(() => isSceneMode.value && (props.form?.commands ?? []).some((c: any) => c.id === props.projector.id));

const isOn = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.projector.id && c.type === 'POWER');
        if (cmd) return cmd.command?.command === 'ON';
        return false;
    }
    return (
        props.projector?.latest_data?.powerState === 'on' ||
        props.projector?.latest_data?.context?.powerState === 'on' ||
        props.projector?.control?.powerState === 'on'
    );
});

const hasCommand = (type: string) => {
    if (!isSceneMode.value) return true;
    return (props.form?.commands ?? []).some((c: any) => c.id === props.projector.id && c.type === type);
};

const send = async (value: string, onError?: () => void) => {
    processing.value = true;

    if (isSceneMode.value) {
        emit(
            'send-command',
            {
                id: props.projector?.id,
                command: { command: value },
                type: value === 'ON' || value === 'OFF' ? 'POWER' : value,
            },
            (ok: boolean) => {
                if (!ok) onError?.();
                processing.value = false;
            },
        );
    } else {
        try {
            await axios.post(remoteCommand({ remote: props.projector.id }).url, {
                command: value,
                parameter: 'default',
            });
        } catch {
            onError?.();
        } finally {
            processing.value = false;
        }
    }
};

const togglePower = async () => {
    if (processing.value) return;
    const newState = !isOn.value;

    props.projector.latest_data = {
        ...(props.projector.latest_data ?? {}),
        powerState: newState ? 'on' : 'off',
    };

    if (isSceneMode.value) {
        if (newState) {
            send('ON');
        } else {
            ['MUTE', 'FREEZE', 'ZOOM IN', 'ZOOM OUT', 'VOL UP', 'VOL DOWN'].forEach((type) => {
                emit('send-command', { id: props.projector.id, type, remove: true });
            });
            send('OFF');
        }

        return;
    }

    await send(newState ? 'ON' : 'OFF', () => {
        props.projector.latest_data = {
            ...(props.projector.latest_data ?? {}),
            powerState: newState ? 'off' : 'on',
        };
    });
};

const isSmallMobile = useMediaQuery('(max-width: 479px)');
</script>
<template>
    <Card
        :class="[
            'xs:p-4 h-full p-3',
            isOn ? 'bg-controls-active' : 'bg-controls-default',
            isSceneMode ? (isSceneActive ? 'bg-controls-default opacity-100' : 'bg-controls-active opacity-50') : '',
        ]"
    >
        <CardContent class="flex h-full flex-col justify-between gap-1 p-0">
            <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">{{ props.projector.name }}</Label>

            <div class="flex h-full flex-row">
                <div v-if="!isSmallMobile" class="flex basis-1/6 flex-col items-center justify-center gap-2 border-r">
                    <Icon icon="bi:projector" class="size-8 md:size-10" />
                </div>
                <div class="xs:basis-5/6 grid w-full grid-cols-5">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Button
                            :variant="isOn && hasCommand('POWER') ? 'default' : 'outline'"
                            class="xs:size-8 size-7"
                            :disabled="processing"
                            @click="togglePower"
                        >
                            <Icon icon="tabler:power" />
                        </Button>
                        <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                            {{ $t('device.controls.power') }}
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('MUTE')">
                            <Icon icon="tabler:volume" />
                        </Button>
                        <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                            {{ $t('device.controls.mute') }}
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('FREEZE')">
                            <Icon icon="tabler:pause" />
                        </Button>
                        <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                            {{ $t('device.controls.freeze') }}
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex flex-col space-y-2 text-center">
                            <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('ZOOM IN')">
                                <Icon icon="tabler:zoom-in" />
                            </Button>
                            <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.zoom') }}
                            </span>
                            <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('ZOOM OUT')">
                                <Icon icon="tabler:zoom-out" />
                            </Button>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex flex-col space-y-2 text-center">
                            <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('VOL UP')">
                                <Icon icon="tabler:plus" />
                            </Button>
                            <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.vol') }}
                            </span>
                            <Button variant="outline" class="xs:size-8 size-7" :disabled="!isOn" @click="send('VOL DOWN')">
                                <Icon icon="tabler:minus" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
