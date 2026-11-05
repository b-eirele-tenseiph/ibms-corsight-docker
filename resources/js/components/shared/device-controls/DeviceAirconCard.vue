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
    aircon: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const MIN_TEMP = 17;
const MAX_TEMP = 35;

const isSceneMode = computed(() => !!props.form);
const processing = ref(false);
const isSceneActive = computed(() => isSceneMode.value && commandTypes.value.size > 0);
const commandTypes = computed(() => new Set((props.form?.commands ?? []).filter((c: any) => c.id === props.aircon.id).map((c: any) => c.type)));

const isOn = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.aircon.id && c.type === 'powerState');
        if (cmd) return cmd.command?.parameter === 'on';
    }
    return props.aircon.latest_data?.powerState === 'on';
});

const deviceMode = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.aircon.id && c.type === 'mode');
        if (cmd) return cmd.command?.parameter;
    }
    return props.aircon.latest_data?.mode ?? '1';
});

const deviceFan = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.aircon.id && c.type === 'fanSpeed');
        if (cmd) return cmd.command?.parameter;
    }
    return props.aircon.latest_data?.fanSpeed ?? '1';
});

const getInitialTemp = () => {
    const fromCommands = (props.form?.commands ?? []).find((c: any) => c.id === props.aircon.id && c.type === 'temperature')?.command?.parameter;
    return Number(fromCommands ?? props.aircon.latest_data?.temperature ?? 26);
};

const localTemp = ref(getInitialTemp());

const hasCommand = (type: string) => !isSceneMode.value || commandTypes.value.has(type);

const send = async (command: string, parameter?: string, onError?: () => void) => {
    processing.value = true;

    if (isSceneMode.value) {
        emit('send-command', { id: props.aircon?.id, command: { command, parameter }, type: command }, (ok: boolean) => {
            if (!ok) onError?.();
            processing.value = false;
        });
    } else {
        try {
            await axios.post(remoteCommand({ remote: props.aircon.id }).url, { command, parameter });
        } catch {
            onError?.();
        } finally {
            processing.value = false;
        }
    }
};

const togglePower = async (newState: boolean) => {
    if (processing.value) return;
    if (isSceneMode.value) {
        if (newState) {
            send('powerState', 'on');
            send('mode', deviceMode.value);
            send('fanSpeed', deviceFan.value);
            send('temperature', String(localTemp.value));
        } else {
            localTemp.value = getInitialTemp();
            send('powerState', 'off');
            ['mode', 'fanSpeed', 'temperature'].forEach((type) => {
                emit('send-command', { id: props.aircon.id, type, remove: true });
            });
        }

        return;
    }

    const oldValue = isOn.value;
    props.aircon.latest_data ??= {};
    props.aircon.latest_data.powerState = newState ? 'on' : 'off';

    await send('powerState', newState ? 'on' : 'off', () => {
        props.aircon.latest_data ??= {};
        props.aircon.latest_data.powerState = oldValue ? 'on' : 'off';
    });
};

const setMode = async (newValue: string) => {
    if (isSceneMode.value) {
        await send('mode', newValue);
        return;
    }

    const oldValue = deviceMode.value;
    props.aircon.latest_data ??= {};
    props.aircon.latest_data.mode = newValue;

    await send('mode', newValue, () => {
        props.aircon.latest_data ??= {};
        props.aircon.latest_data.mode = oldValue;
    });
};

const setFan = async (newValue: string) => {
    if (isSceneMode.value) {
        await send('fanSpeed', newValue);
        return;
    }

    const oldValue = deviceFan.value;
    props.aircon.latest_data ??= {};
    props.aircon.latest_data.fanSpeed = newValue;

    await send('fanSpeed', newValue, () => {
        props.aircon.latest_data ??= {};
        props.aircon.latest_data.fanSpeed = oldValue;
    });
};

const adjustTemp = (change: number) => {
    localTemp.value = Math.max(MIN_TEMP, Math.min(MAX_TEMP, localTemp.value + change));
    send('temperature', String(localTemp.value), () => {
        localTemp.value = getInitialTemp();
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
            <div class="flex items-start justify-between gap-4">
                <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">{{ props.aircon.name }}</Label>
                <span v-if="isSmallMobile" class="text-sm font-bold text-nowrap"> {{ localTemp }} <sup class="text-xxs">°C</sup> </span>
            </div>

            <div class="flex h-full flex-row">
                <div v-if="!isSmallMobile" class="flex basis-1/6 flex-col items-center justify-center gap-2 border-r">
                    <Icon icon="mynaui:air-conditioner" class="size-8 md:size-10" />
                    <span class="text-xs font-bold sm:text-lg">
                        {{ localTemp }}
                        <sup class="text-xxs">°C</sup>
                    </span>
                </div>

                <div class="xs:basis-5/6 grid w-full grid-cols-[1fr_1.5fr_1.5fr_1fr] gap-2">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Button
                            :variant="isOn && hasCommand('powerState') ? 'default' : 'outline'"
                            class="xs:size-8 size-7"
                            :disabled="processing"
                            @click="togglePower(!isOn)"
                        >
                            <Icon icon="tabler:power" />
                        </Button>
                        <span class="text-secondary-text xs:block hidden text-xs">
                            {{ $t('device.controls.power') }}
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-full space-y-2">
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    :variant="deviceMode == '1' && hasCommand('mode') ? 'default' : 'outline'"
                                    :disabled="processing || !isOn"
                                    class="xs:size-8 size-7"
                                    @click="setMode('1')"
                                >
                                    <Icon icon="mynaui:letter-a-solid" />
                                </Button>
                                <Button
                                    :variant="deviceMode == '2' && hasCommand('mode') ? 'default' : 'outline'"
                                    :disabled="processing || !isOn"
                                    class="xs:size-8 size-7"
                                    @click="setMode('2')"
                                >
                                    <Icon icon="tabler:snowflake" />
                                </Button>
                            </div>
                            <p class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.mode') }}
                            </p>
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    :variant="deviceMode == '3' && hasCommand('mode') ? 'default' : 'outline'"
                                    :disabled="processing || !isOn"
                                    class="xs:size-8 size-7"
                                    @click="setMode('3')"
                                >
                                    <Icon icon="tabler:droplet-off" />
                                </Button>
                                <Button
                                    :variant="deviceMode == '4' && hasCommand('mode') ? 'default' : 'outline'"
                                    :disabled="processing || !isOn"
                                    class="xs:size-8 size-7"
                                    @click="setMode('4')"
                                >
                                    <Icon icon="tabler:car-fan" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-full space-y-2">
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    v-for="s in ['1', '2']"
                                    :key="s"
                                    :variant="deviceFan == s && hasCommand('fanSpeed') ? 'default' : 'outline'"
                                    class="xs:size-8 size-7"
                                    :disabled="processing || !isOn"
                                    @click="setFan(s)"
                                >
                                    <Icon :icon="`tabler:car-fan-${s === '1' ? 'auto' : Number(s) - 1}`" />
                                </Button>
                            </div>
                            <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.fan_speed') }}
                            </span>
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    v-for="s in ['3', '4']"
                                    :key="s"
                                    :variant="deviceFan == s && hasCommand('fanSpeed') ? 'default' : 'outline'"
                                    class="xs:size-8 size-7"
                                    :disabled="processing || !isOn"
                                    @click="setFan(s)"
                                >
                                    <Icon :icon="`tabler:car-fan-${Number(s) - 1}`" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex flex-col space-y-2 text-center">
                            <Button
                                variant="outline"
                                class="xs:size-8 size-7"
                                :disabled="processing || !isOn || localTemp >= MAX_TEMP"
                                @click="adjustTemp(1)"
                            >
                                <Icon icon="tabler:chevron-up" />
                            </Button>

                            <Button
                                variant="outline"
                                class="xs:size-8 size-7"
                                :disabled="processing || !isOn || localTemp <= MIN_TEMP"
                                @click="adjustTemp(-1)"
                            >
                                <Icon icon="tabler:chevron-down" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
