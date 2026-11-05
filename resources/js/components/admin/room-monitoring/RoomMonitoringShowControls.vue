<script setup lang="ts">
import {
    AirconCard,
    BlindTiltCard,
    ControlCard,
    CurtainCard,
    ProjectorCard,
    SwitchCard,
    TelevisionCard,
    ThermostatCard,
} from '@/components/shared/device-controls';
import { ModalShowForm } from '@/components/shared/modals';
import { RoomMonitoringData } from '@/types';
import { computed } from 'vue';
import { MonitoringControls, SkeletonLoader } from '.';

const props = defineProps<{
    data: RoomMonitoringData;
    isOpen: boolean;
    selectedRoom: string | null;
    isLoading: boolean;
}>();

const { device_icons } = props.data.deviceIcon || {};

const isOn = (val: any, target: string) => String(val || '').toUpperCase() === target;
const byPowerState = (data: any) => [isOn(data?.latest_data?.powerState, 'ON')];

const indicators = {
    switch: (data: any) => {
        const filtered = Object.entries(data?.latest_data).filter(([key]) => key.startsWith('switch_'));
        return filtered.map(([, val]: any) => isOn(val?.value, 'HIGH'));
    },
    device: (data: any) => {
        const latest = data?.latest_data ?? {};
        if (!latest.powerState && !latest.power) return [];
        return [
            Object.entries({ powerState: 'ON', power: 'ON' }).some(([key, val]) => isOn(latest[key], val)),
        ];
    },
    thermostat: (data: any) => [isOn(data?.latest_data?.slave_power?.value, 'ON')],
    aircon: byPowerState,
    projector: byPowerState,
    television: byPowerState,
};

const deviceConfigs = [
    {
        get data() {
            return props.data.switch;
        },
        key: 'switch',
        indicators: indicators.switch,
        component: SwitchCard,
        propsParam: (d: any) => ({ light: d }),
    },
    {
        get data() {
            return props.data.device;
        },
        key: 'device',
        indicators: indicators.device,
        component: ControlCard,
        propsParam: (d: any) => ({ appliance: d, icon: device_icons }),
    },
    {
        get data() {
            return props.data.aircon;
        },
        key: 'aircon',
        indicators: indicators.aircon,
        component: AirconCard,
        propsParam: (d: any) => ({ aircon: d }),
    },
    {
        get data() {
            return props.data.thermostat;
        },
        key: 'thermo',
        indicators: indicators.thermostat,
        component: ThermostatCard,
        propsParam: (d: any) => ({ thermostat: d }),
    },
    {
        get data() {
            return props.data.projector;
        },
        key: 'projector',
        indicators: indicators.projector,
        component: ProjectorCard,
        propsParam: (d: any) => ({ projector: d }),
    },
    {
        get data() {
            return props.data.television;
        },
        key: 'television',
        indicators: indicators.television,
        component: TelevisionCard,
        propsParam: (d: any) => ({ television: d }),
    },
    {
        get data() {
            return props.data.curtain;
        },
        key: 'curtain',
        indicators: () => [],
        component: CurtainCard,
        propsParam: (d: any) => ({ curtain: d }),
    },
    {
        get data() {
            return props.data.blindtilt;
        },
        key: 'blind',
        indicators: () => [],
        component: BlindTiltCard,
        propsParam: (d: any) => ({ blind: d }),
    },
];

const accordionItems = computed(() =>
    deviceConfigs.flatMap(({ data: list = [], key, indicators, component, propsParam }) =>
        list
            .filter((data: any) => data.room_area === props.selectedRoom)
            .map((data: any) => ({
                id: `${key}-${data.id}`,
                type: key,
                title: data.name,
                indicators: indicators(data),
                component,
                props: propsParam(data),
            })),
    ).sort((a, b) => a.title.localeCompare(b.title)),
);
</script>
<template>
    <ModalShowForm :open="isOpen" :title="selectedRoom" @close="$emit('close')">
        <template v-if="isLoading">
            <SkeletonLoader />
        </template>
        <template v-else-if="accordionItems.length">
            <MonitoringControls :accordionItems="accordionItems" />
        </template>
        <template v-else>
            <div class="flex justify-center py-6 font-semibold">
                {{ $t('modal.alert.room_monitoring_no_devices') }}
            </div>
        </template>
    </ModalShowForm>
</template>
