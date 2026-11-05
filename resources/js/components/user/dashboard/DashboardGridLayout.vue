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
import { AIPeopleSensorCard, SensorCard } from '@/components/shared/device-telemetry';
import { DashboardUserData } from '@/types';
import { inject } from 'vue';
import GridItemLayout from './DashboardGridItemLayout.vue';
import { CO2TempHumidCard, OccupancyTempLightCard, OutdoorMeterCard } from './graphs';

const props = defineProps<{
    dashboardData: DashboardUserData;
}>();

const setting = inject('gridSetting') as { column: any; drag: boolean };
const { device_icons, sensor_icons } = props.dashboardData.deviceIcon || {};
</script>
<template>
    <div class="grid-stack">
        <template v-for="people in dashboardData.ai_sensor" :key="people.id">
            <GridItemLayout
                :data="{ id: people.id, type: people.model }"
                :position="people.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
                :isLocked="people.name === 'Daily Occupancy'"
            >
                <AIPeopleSensorCard :people="people" />
            </GridItemLayout>
        </template>

        <template v-for="light in dashboardData.switch" :key="light.id">
            <GridItemLayout
                :data="{ id: light.id, model: light.model }"
                :position="light.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
            >
                <SwitchCard :light="light" />
            </GridItemLayout>
        </template>

        <template v-for="thermo in dashboardData.thermostat" :key="thermo.id">
            <GridItemLayout
                :data="{ id: thermo.id, model: thermo.model }"
                :position="thermo.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
            >
                <ThermostatCard :thermostat="thermo" />
            </GridItemLayout>
        </template>

        <template v-for="sensor in dashboardData.sensor" :key="sensor.id">
            <GridItemLayout
                :data="{ id: sensor.id, type: sensor.model }"
                :position="sensor.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
            >
                <SensorCard :sensor="sensor" :icon="sensor_icons" />
            </GridItemLayout>
        </template>

        <template v-for="occupancy in dashboardData.occu_temp_light" :key="occupancy.id">
            <GridItemLayout
                :data="{ id: occupancy.id, type: occupancy.model }"
                :position="occupancy.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
            >
                <OccupancyTempLightCard :data="occupancy" />
            </GridItemLayout>
        </template>

        <template v-for="co2 in dashboardData.co2_temp_humid" :key="co2.id">
            <GridItemLayout
                :data="{ id: co2.id, type: co2.model }"
                :position="co2.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="lorawanDevice"
                :class="{ shaky: setting.drag }"
            >
                <CO2TempHumidCard :data="co2" />
            </GridItemLayout>
        </template>

        <template v-for="device in dashboardData.device" :key="device.id">
            <GridItemLayout
                :data="{ id: device.id, type: device.model }"
                :position="device.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotDevice"
                :class="{ shaky: setting.drag }"
            >
                <ControlCard :appliance="device" :icon="device_icons" />
            </GridItemLayout>
        </template>

        <template v-for="curtain in dashboardData.curtain" :key="curtain.id">
            <GridItemLayout
                :data="{ id: curtain.id, type: curtain.type }"
                :position="curtain.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotDevice"
                :class="{ shaky: setting.drag }"
            >
                <CurtainCard :curtain="curtain" />
            </GridItemLayout>
        </template>

        <template v-for="hygrometer in dashboardData.thermohygrometer" :key="hygrometer.id">
            <GridItemLayout
                :data="{ id: hygrometer.id, type: hygrometer.type }"
                :position="hygrometer.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotDevice"
                :class="{ shaky: setting.drag }"
            >
                <OutdoorMeterCard :data="hygrometer" />
            </GridItemLayout>
        </template>

        <template v-for="blind in dashboardData.blindtilt" :key="blind.id">
            <GridItemLayout
                :data="{ id: blind.id, type: blind.type }"
                :position="blind.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotDevice"
                :class="{ shaky: setting.drag }"
            >
                <BlindTiltCard :blind="blind" />
            </GridItemLayout>
        </template>

        <template v-for="ac in dashboardData.aircon" :key="ac.id">
            <GridItemLayout
                :data="{ id: ac.id, type: ac.type }"
                :position="ac.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotRemote"
                :class="{ shaky: setting.drag }"
            >
                <AirconCard :aircon="ac" />
            </GridItemLayout>
        </template>

        <template v-for="projector in dashboardData.projector" :key="projector.id">
            <GridItemLayout
                :data="{ id: projector.id, type: projector.type }"
                :position="projector.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotRemote"
                :class="{ shaky: setting.drag }"
            >
                <ProjectorCard :projector="projector" />
            </GridItemLayout>
        </template>

        <template v-for="tv in dashboardData.television" :key="tv.id">
            <GridItemLayout
                :data="{ id: tv.id, type: tv.type }"
                :position="tv.positions?.find((p: any) => p.context.c == setting.column)?.context"
                type="switchBotRemote"
                :class="{ shaky: setting.drag }"
            >
                <TelevisionCard :television="tv" />
            </GridItemLayout>
        </template>
    </div>
</template>
