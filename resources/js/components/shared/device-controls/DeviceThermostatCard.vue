<!-- eslint-disable vue/no-mutating-props -->
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { roundToStep } from '@/lib/utils';
import { SceneCommandPayload } from '@/types';
import { command as deviceCommand } from '@/routes/lorawan_devices';
import { Icon } from '@iconify/vue';
import { clamp, useMediaQuery } from '@vueuse/core';
import axios from 'axios';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    thermostat: Record<string, any>;
    form?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'send-command', payload: SceneCommandPayload, done?: (ok: boolean) => void): void;
}>();

const MIN_TEMP = 17;
const MAX_TEMP = 35;
const TEMP_STEP = 0.5;

const isSceneMode = computed(() => !!props.form);
const processing = ref(false);
const commandTypes = computed(() => new Set((props.form?.commands ?? []).filter((c: any) => c.id === props.thermostat.id).map((c: any) => c.type)));
const isSceneActive = computed(() => isSceneMode.value && commandTypes.value.size > 0);

const isOn = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.thermostat.id && c.type === 'POWER');
        if (cmd) return cmd.command?.command === 'powerOn';
        return false;
    }
    return props.thermostat.latest_data?.slave_power?.value === 'on';
});

const deviceFan = computed(() => {
    if (isSceneMode.value) {
        const cmd = (props.form?.commands ?? []).find((c: any) => c.id === props.thermostat.id && c.type === 'fanSpeed');
        if (cmd) return cmd.command?.command?.replace('fanSpeed', '') ?? 'auto';
    }
    return String(props.thermostat.latest_data?.fan_speed?.value ?? 'auto');
});

const roomTemp = computed(() => props.thermostat.latest_data?.temperature?.value ?? 0);

const hasCommand = (type: string) => !isSceneMode.value || commandTypes.value.has(type);

const findCommand = (type: string) => (props.form?.commands ?? []).find((c: any) => c.id === props.thermostat.id && c.type === type);

const getInitialTemp = () => {
    const fromCommands = findCommand('changeTemperature');
    if (fromCommands) return Number(fromCommands.command?.parameter?.new_setpoint ?? 0);
    return Number(props.thermostat.latest_data?.temperature?.value ?? 0);
};

const localTemp = ref(getInitialTemp());

const hasChanged = computed(() => localTemp.value !== getInitialTemp());

const send = async (commandData: string, parameter?: any, onError?: () => void) => {
    processing.value = true;

    if (isSceneMode.value) {
        emit(
            'send-command',
            {
                id: props.thermostat?.id,
                command: { command: commandData, ...(parameter && { parameter }) },
                type: commandData === 'powerOn' || commandData === 'powerOff' ? 'POWER' : commandData.includes('fanSpeed') ? 'fanSpeed' : commandData,
            },
            (ok: boolean) => {
                if (!ok) onError?.();
                processing.value = false;
            },
        );
    } else {
        try {
            await axios.post(deviceCommand({ device: props.thermostat.id }).url, {
                command: commandData,
                ...(parameter && { parameter }),
            });
        } catch {
            onError?.();
        } finally {
            processing.value = false;
        }
    }
};

const togglePower = async (newState: boolean) => {
    if (isSceneMode.value) {
        if (newState) {
            send('powerOn');
            send(`fanSpeed${deviceFan.value}`);
            send('changeTemperature', { new_setpoint: localTemp.value });
        } else {
            localTemp.value = getInitialTemp();
            send('powerOff');
            ['fanSpeed', 'changeTemperature'].forEach((type) => {
                emit('send-command', { id: props.thermostat.id, type, remove: true });
            });
        }

        return;
    }

    const oldValue = isOn.value;
    props.thermostat.latest_data = {
        ...(props.thermostat.latest_data ?? {}),
        slave_power: { ...(props.thermostat.latest_data?.slave_power ?? {}), value: newState ? 'on' : 'off' },
    };

    await send(newState ? 'powerOn' : 'powerOff', undefined, () => {
        props.thermostat.latest_data = {
            ...(props.thermostat.latest_data ?? {}),
            slave_power: { ...(props.thermostat.latest_data?.slave_power ?? {}), value: oldValue ? 'on' : 'off' },
        };
    });
};

const setFan = async (newValue: string) => {
    if (isSceneMode.value) {
        await send(`fanSpeed${newValue}`, undefined);
        return;
    }

    const oldValue = deviceFan.value;
    props.thermostat.latest_data = {
        ...(props.thermostat.latest_data ?? {}),
        fan_speed: { ...(props.thermostat.latest_data?.fan_speed ?? {}), value: newValue },
    };

    await send(`fanSpeed${newValue}`, undefined, () => {
        props.thermostat.latest_data = {
            ...(props.thermostat.latest_data ?? {}),
            fan_speed: { ...(props.thermostat.latest_data?.fan_speed ?? {}), value: oldValue },
        };
    });
};

const adjustTemp = (change: number) => {
    localTemp.value = clamp(roundToStep(localTemp.value + change, TEMP_STEP), MIN_TEMP, MAX_TEMP);

    clearTimeout(debounceTimeout.value);
    debounceTimeout.value = setTimeout(() => {
        send('changeTemperature', { new_setpoint: localTemp.value }, () => {
            localTemp.value = getInitialTemp();
        });
    }, 1000);
};

const debounceTimeout = ref<ReturnType<typeof setTimeout> | undefined>(undefined);

onUnmounted(() => {
    clearTimeout(debounceTimeout.value);
});

const isSmallMobile = useMediaQuery('(max-width: 479px)');

watch(
    () => props.form?.commands,
    () => {
        localTemp.value = getInitialTemp();
    },
    { immediate: true, deep: true },
);
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
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <Label class="fluid-text-xs-sm truncate leading-5 font-semibold">{{ props.thermostat.name }}</Label>
                <div v-if="isSmallMobile" class="flex flex-col items-end text-sm font-bold sm:text-lg">
                    <span v-if="hasChanged">{{ localTemp }}<sup class="text-xxs">°C</sup></span>
                    <span v-else>{{ roomTemp }} <sup class="text-xxs">°C</sup></span>
                    <span class="text-secondary-text text-xxs font-normal text-nowrap">
                        {{ $t('device.controls.room_temp') }}
                    </span>
                </div>
            </div>

            <div class="flex h-full flex-row">
                <!-- Room Temp (Desktop only) -->
                <div v-if="!isSmallMobile" class="flex basis-1/3 flex-col items-center justify-center gap-2 border-r">
                    <Icon icon="mynaui:air-conditioner" class="size-8 md:size-10" />
                    <div class="flex flex-col items-center">
                        <span v-if="hasChanged" class="text-destructive text-xs font-bold sm:text-lg">
                            {{ localTemp }} <sup class="text-xxs">°C</sup></span
                        >
                        <span v-else class="text-xs font-bold sm:text-lg"> {{ roomTemp }} <sup class="text-xxs">°C</sup> </span>
                        <span class="text-secondary-text text-xxs sm:text-xs">
                            {{ $t('device.controls.room_temp') }}
                        </span>
                    </div>
                </div>

                <!-- Controls -->
                <div class="xs:basis-5/6 grid w-full grid-cols-[1fr_1fr_1fr] gap-2">
                    <!-- Power -->
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Button
                            :variant="isOn && hasCommand('POWER') ? 'default' : 'outline'"
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

                    <!-- Fan -->
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-full space-y-2">
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    v-for="s in ['auto', '1']"
                                    :key="s"
                                    :variant="deviceFan == s && hasCommand('fanSpeed') ? 'default' : 'outline'"
                                    class="xs:size-8 size-7"
                                    :disabled="processing || !isOn"
                                    @click="setFan(s)"
                                >
                                    <Icon :icon="`tabler:car-fan-${s}`" />
                                </Button>
                            </div>
                            <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.fan_speed') }}
                            </span>
                            <div class="flex flex-row justify-center gap-2">
                                <Button
                                    v-for="s in ['2', '3']"
                                    :key="s"
                                    :variant="deviceFan == s && hasCommand('fanSpeed') ? 'default' : 'outline'"
                                    class="xs:size-8 size-7"
                                    :disabled="processing || !isOn"
                                    @click="setFan(s)"
                                >
                                    <Icon :icon="`tabler:car-fan-${s}`" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Temperature -->
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex flex-col space-y-2 text-center">
                            <Button variant="outline" class="xs:size-8 size-7" :disabled="processing || !isOn" @click="adjustTemp(TEMP_STEP)">
                                <Icon icon="tabler:chevron-up" />
                            </Button>
                            <span class="text-secondary-text xs:block hidden w-full truncate text-center text-xs">
                                {{ $t('device.controls.temp') }}
                            </span>

                            <Button variant="outline" class="xs:size-8 size-7" :disabled="processing || !isOn" @click="adjustTemp(-TEMP_STEP)">
                                <Icon icon="tabler:chevron-down" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
