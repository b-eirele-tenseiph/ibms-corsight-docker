<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { roundToStep } from '@/lib/utils';
import { command as remoteCommand } from '@/routes/lorawan_devices';
import { AirconData, ThermostatContextData } from '@/types';
import { Icon } from '@iconify/vue';
import { clamp } from '@vueuse/core';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{
    thermostat: AirconData;
    data?: ThermostatContextData;
}>();

const { isExtraSmallMobile } = useBreakpoints();

// Track if request is currently in progress
const processing = ref(false);

const localThermoData = ref({ ...props.thermostat });

const sendCommand = async (commandData: string, parameter?: any, onError?: () => void) => {
    try {
        await axios.post(remoteCommand({ device: String(props.thermostat.id) }).url, {
            command: commandData,
            ...(parameter && { parameter }),
        });
    } catch {
        onError?.();
    } finally {
        processing.value = false;
    }
};

const power = reactive({
    value: computed({
        get: () => localThermoData.value.latest_data?.slave_power.value === 'on',
        set: (newValue) => (localThermoData.value.latest_data.slave_power.value = newValue ? 'on' : 'off'),
    }),
    async onChange(newValue: any) {
        const oldValue = this.value;
        this.value = newValue;

        await sendCommand(newValue ? 'powerOn' : 'powerOff', undefined, () => setTimeout(() => (this.value = oldValue), 300));
    },
});

const fan = reactive({
    value: computed({
        get: () => localThermoData.value.latest_data?.fan_speed.value ?? 'auto',
        set: (newValue) => (localThermoData.value.latest_data.fan_speed.value = newValue),
    }),
    async onChange(newValue: any) {
        const oldValue = this.value;
        this.value = newValue;

        await sendCommand(`fanSpeed${newValue}`, undefined, () => setTimeout(() => (this.value = oldValue), 300));
    },
});

const temperature = reactive({
    MIN_TEMP: 17,
    MAX_TEMP: 35,
    STEP: 0.5,
    value: computed({
        get: () => Number(localThermoData.value.latest_data?.current_setpoint.value ?? 0),
        set: (newValue) => (localThermoData.value.latest_data.current_setpoint.value = newValue),
    }),
    roomTemp: computed(() => localThermoData.value.latest_data?.temperature.value),
    current: localThermoData.value?.latest_data?.current_setpoint?.value,
    debounceTimeout: null as any,
    get hasChanged() {
        return this.current !== this.value;
    },
    async onChange() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(async () => {
            await sendCommand('changeTemperature', { new_setpoint: this.value }, () => (this.value = this.current));
            this.current = this.value;
        }, 1000);
    },
    adjustTemp(delta: number) {
        const rounded = roundToStep(temperature.value + delta, this.STEP);
        temperature.value = clamp(rounded, this.MIN_TEMP, this.MAX_TEMP);
        this.onChange();
    },
});

const hasChanged = defineModel('hasChanged', { type: Boolean, default: false });

watch(
    () => temperature.hasChanged,
    (newValue) => {
        hasChanged.value = newValue;
    },
);
</script>

<template>
    <div :class="[isExtraSmallMobile ? 'flex flex-col gap-2' : 'flex flex-wrap items-center justify-start gap-5']">
        <!-- Power Button -->
        <div>
            <Button
                :variant="power.value ? 'default' : 'outline'"
                class="xs:size-8 size-7"
                :disabled="processing"
                @click="power.onChange(!power.value)"
                aria-label="Toggle power"
            >
                <Icon icon="tabler:power" />
            </Button>
        </div>

        <!-- Fan Speed Controls -->
        <div class="flex flex-row gap-1">
            <template v-for="s in ['auto', 1, 2, 3]" :key="s">
                <Button
                    :variant="fan.value === s ? 'default' : 'outline'"
                    class="xs:size-8 size-7"
                    :disabled="processing || !power.value"
                    @click="fan.onChange(s)"
                    :aria-label="`Fan ${s}`"
                >
                    <Icon :icon="s === 'auto' ? 'tabler:car-fan-auto' : `tabler:car-fan-${s}`" />
                </Button>
            </template>
        </div>

        <!-- Temperature Controls -->
        <div class="flex flex-row items-center gap-1">
            <Button
                variant="outline"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value"
                @click="temperature.adjustTemp(temperature.STEP)"
                aria-label="Temp up"
            >
                <Icon icon="tabler:chevron-up" />
            </Button>
            <Button
                variant="outline"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value"
                @click="temperature.adjustTemp(-temperature.STEP)"
                aria-label="Temp down"
            >
                <Icon icon="tabler:chevron-down" />
            </Button>
        </div>
    </div>
</template>
