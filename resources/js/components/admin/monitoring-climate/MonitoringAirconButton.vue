<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { command as remoteCommand } from '@/routes/switch_bot_remotes';
import { AirconData } from '@/types';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';

const { isExtraSmallMobile } = useBreakpoints();

const props = defineProps<{
    aircon: AirconData;
}>();

const processing = ref(false);

const emit = defineEmits<{
    (e: 'update:aircon', value: any): void;
}>();

const localAirconData = ref({ ...props.aircon });

watch(
    () => props.aircon,
    (newValue) => {
        localAirconData.value = { ...newValue };
    },
    { deep: true },
);

const sendCommand = async (command: any, parameter?: any, onError?: () => void) => {
    processing.value = true;

    try {
        await axios.post(remoteCommand({ remote: String(props.aircon!.id) }).url, {
            command,
            parameter,
        });
        emit('update:aircon', localAirconData.value);
    } catch {
        onError?.();
    } finally {
        processing.value = false;
    }
};

const power = reactive({
    value: computed({
        get: () => localAirconData.value.latest_data?.powerState === 'on',
        set: (newValue) => (localAirconData.value.latest_data.powerState = newValue ? 'on' : 'off'),
    }),
    async onChange(newState: any) {
        const oldValue = this.value;
        this.value = newState;

        await sendCommand('powerState', newState ? 'on' : 'off', () => setTimeout(() => (this.value = oldValue), 300));
    },
});

const mode = reactive({
    value: computed({
        get: () => localAirconData.value.latest_data?.mode ?? '1',
        set: (newValue) => (localAirconData.value.latest_data.mode = newValue),
    }),
    async onChange(newValue: any) {
        const oldValue = this.value;
        this.value = newValue;

        await sendCommand('mode', newValue, () => setTimeout(() => (this.value = oldValue), 300));
    },
});

const fan = reactive({
    value: computed({
        get: () => localAirconData.value.latest_data?.fanSpeed ?? '2',
        set: (newValue) => (localAirconData.value.latest_data.fanSpeed = newValue),
    }),
    async onChange(newValue: any) {
        const oldValue = this.value;
        this.value = newValue;

        await sendCommand('fanSpeed', newValue, () => setTimeout(() => (this.value = oldValue), 300));
    },
});

const temperature = reactive({
    MIN_TEMP: 17,
    MAX_TEMP: 35,
    value: computed({
        get: () => Number(localAirconData.value.latest_data?.temperature ?? 0),
        set: (newValue) => (localAirconData.value.latest_data.temperature = newValue),
    }),
    temperature: localAirconData.value.latest_data?.temperature ?? 0,
    debounceTimeout: null as any,
    async onChange() {
        clearTimeout(this.debounceTimeout);

        this.debounceTimeout = setTimeout(() => {
            sendCommand('temperature', this.value, () => setTimeout(() => (this.value = this.temperature), 300));
        }, 1000);
    },
    adjustTemp(change: number) {
        const newTemp = temperature.value + change;

        if (newTemp >= this.MIN_TEMP && newTemp <= this.MAX_TEMP) {
            temperature.value = newTemp;
            this.onChange();
        }
    },
});
</script>

<template>
    <div :class="[isExtraSmallMobile ? 'flex flex-col gap-2' : 'flex flex-wrap items-center justify-start gap-4']">
        <!-- Power -->
        <div>
            <Button
                :variant="power.value ? 'default' : 'outline'"
                class="xs:size-8 size-7"
                :disabled="processing"
                @click="power.onChange(!power.value)"
            >
                <Icon icon="tabler:power" />
            </Button>
        </div>

        <!-- Mode (1=Auto, 2=Cool, 3=Dry, 4=Fan) -->
        <div class="flex flex-row gap-1">
            <Button
                v-for="(icon, val) in {
                    1: 'mynaui:letter-a-solid',
                    2: 'tabler:snowflake',
                    3: 'tabler:droplet-off',
                    4: 'tabler:car-fan',
                }"
                :key="val"
                :variant="mode.value == val ? 'default' : 'outline'"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value"
                @click="mode.onChange(String(val))"
            >
                <Icon :icon="icon" />
            </Button>
        </div>

        <!-- Fan  -->
        <div class="flex flex-row gap-1">
            <Button
                v-for="s in ['1', '2', '3', '4']"
                :key="s"
                :variant="fan.value == s ? 'default' : 'outline'"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value"
                @click="fan.onChange(s)"
            >
                <Icon :icon="`tabler:car-fan-${s === '1' ? 'auto' : Number(s) - 1}`" />
            </Button>
        </div>

        <!-- Temperature -->
        <div class="flex flex-row items-center gap-1">
            <Button
                variant="outline"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value || temperature.value >= temperature.MAX_TEMP"
                @click="temperature.adjustTemp(1)"
            >
                <Icon icon="tabler:chevron-up" />
            </Button>
            <Button
                variant="outline"
                class="xs:size-8 size-7"
                :disabled="processing || !power.value || temperature.value <= temperature.MIN_TEMP"
                @click="temperature.adjustTemp(-1)"
            >
                <Icon icon="tabler:chevron-down" />
            </Button>
        </div>
    </div>
</template>
