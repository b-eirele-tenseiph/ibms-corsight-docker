<script setup lang="ts">
import { GridLayout, PromoPopupModal, RoomAreaFilter, ScenesPanel, SkeletonLoader } from '@/components/user/dashboard';
import { useModal } from '@/composables/useModal';
import { UserLayout, UserPageLayout } from '@/layouts/user';
import { gridStore } from '@/routes/user';
import {
    airconLoad,
    blindTiltLoad,
    co2TempHumidLoad,
    curtainLoad,
    deviceLoad,
    occuTempLightLoad,
    outdoorMeterLoad,
    peopleCounterLoad,
    projectorLoad,
    sensorsLoad,
    switchLoad,
    televisionLoad,
    thermostatLoad,
} from '@/routes/user/dashboard';
import {
    DashboardUserDevicesData,
    DeviceAndSensorIcons,
    PalettesAndIcons,
    Promo,
    QueryParams,
    UserDashboardRoom,
    UserGridPositionLayout,
} from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { GridStack, type GridStackOptions } from 'gridstack';
import 'gridstack/dist/gridstack-all.js';
import 'gridstack/dist/gridstack.min.css';
import { computed, onMounted, onUnmounted, provide, reactive, ref, shallowRef, toRef } from 'vue';

const props = defineProps<{
    room: UserDashboardRoom;
    theme: PalettesAndIcons;
    icon: DeviceAndSensorIcons;
    filters: QueryParams;
    hidePromo: boolean;
    promos: Array<Promo>;
}>();

const grid = shallowRef<GridStack | null>(null);

const gridSettings = computed(() => {
    const matched = gridstackInstance.breakpoints.findLast((bp) => window.innerWidth >= bp.w);
    return matched || { c: 2, h: 150 };
});

const gridstackInstance = {
    breakpoints: [
        { w: 420, c: 2, h: 160 },
        { w: 560, c: 3, h: 160 },
        { w: 720, c: 4, h: 160 },
        { w: 920, c: 5, h: 170 },
        { w: 1180, c: 6, h: 180 },
        { w: 1280, c: 7, h: 170 },
        { w: 1440, c: 8, h: 170 },
        { w: 1600, c: 9, h: 170 },
        { w: 1920, c: 10, h: 180 },
        { w: 2560, c: 12, h: 180 },
    ],
    options(): GridStackOptions {
        return {
            margin: 4,
            disableResize: true,
            float: true,
            resizable: { handles: 'none' },
            cellHeight: gridSettings.value.h,
            columnOpts: {
                breakpoints: gridstackInstance.breakpoints.map(({ w, c }) => ({ w, c })),
                breakpointForWindow: true,
                layout: 'moveScale',
            },
        };
    },
    load: () => {
        grid.value = GridStack.init(gridstackInstance.options());

        grid.value?.disable();

        grid.value?.on('dragstart', (event: Event, el: HTMLElement) => {
            el.querySelector('.grid-stack-item-content')?.classList.remove('shaky');
        });

        grid.value?.on('dragstop', (event: Event, el: HTMLElement) => {
            if (!devicesData.dragEnabled) el.querySelector('.grid-stack-item-content')?.classList.add('shaky');
        });

        grid.value?.on('change', () => {
            devicesData.isDirty = true;
        });

        window.addEventListener('resize', () => {
            grid.value?.cellHeight(gridSettings.value.h);
        });
    },
};

const devicesData = reactive<DashboardUserDevicesData>({
    isLoading: false,
    dragEnabled: false,
    switch: [],
    thermostat: [],
    device: [],
    sensor: [],
    ai_sensor: [],
    occu_temp_light: [],
    co2_temp_humid: [],
    curtain: [],
    blindtilt: [],
    thermohygrometer: [],
    aircon: [],
    projector: [],
    television: [],
    isDirty: false,
    currentArea: ref('all-area'),
    toggleDrag: () => {
        devicesData.dragEnabled = !devicesData.dragEnabled;

        if (devicesData.dragEnabled) {
            grid.value?.enable();
        } else {
            if (devicesData.isDirty) {
                const positions = grid.value?.engine.nodes.map(({ id, x, y, w, h, el }: any) => {
                    return {
                        id,
                        x,
                        y,
                        w,
                        h,
                        room_area: props.filters.area || 'all-area',
                        type: el?.getAttribute('data-type'),
                        column: gridSettings.value.c,
                    };
                });

                useForm<{ positions: UserGridPositionLayout[] }>({ positions: positions ?? [] }).post(gridStore.url());

                grid.value?.disable();
            }
        }
        devicesData.isDirty = false;
    },
    loadControls: async () => {
        devicesData.isLoading = true;

        const loadControlData = async (routeFn: (options?: any) => { url: string }, key: keyof typeof devicesData) => {
            try {
                (devicesData as any)[key] = (
                    await axios.get(routeFn({ room: props.room.number }).url, { params: { area: props.filters.area } })
                ).data;
            } catch {}
        };

        await Promise.all([
            loadControlData(switchLoad, 'switch'),
            loadControlData(thermostatLoad, 'thermostat'),
            loadControlData(deviceLoad, 'device'),
            loadControlData(curtainLoad, 'curtain'),
            loadControlData(outdoorMeterLoad, 'thermohygrometer'),
            loadControlData(airconLoad, 'aircon'),
            loadControlData(projectorLoad, 'projector'),
            loadControlData(televisionLoad, 'television'),
            loadControlData(peopleCounterLoad, 'ai_sensor'),
            loadControlData(blindTiltLoad, 'blindtilt'),
            loadControlData(sensorsLoad, 'sensor'),
            loadControlData(occuTempLightLoad, 'occu_temp_light'),
            loadControlData(co2TempHumidLoad, 'co2_temp_humid'),
        ]);

        devicesData.isLoading = false;
    },
});

onMounted(async () => {
    await devicesData.loadControls();
    gridstackInstance.load();
});

const gridSetting = reactive({
    column: gridSettings.value.c,
    drag: toRef(devicesData, 'dragEnabled'),
});

provide('theme', { palettes: props.theme.palettes, icons: props.theme.icons });
provide('gridSetting', gridSetting);
provide('icon', props.icon);

const { stopListening: stopLorawanDevice, listen: listenLorawanDevice } = useEcho(
    `room.${props.room.id}`,
    '.lorawan_device_data.new',
    (data: any) => {
        Object.keys(data).forEach((key) => {
            (devicesData as any)[key]?.forEach((device: any) => {
                (data[key] ?? [])
                    .filter((item: any) => item.device_id === device.id)
                    .forEach((item: any) => {
                        if (device.latest_data) {
                            device.latest_data[item.type] = item.context;
                        }

                        if (device.series) {
                            const series = device.series.find((s: any) => s.name === item.type);
                            series?.data.unshift([item.received_at, item.context.value]);

                            if (series?.data.length > 10) {
                                series?.data.pop();
                            }
                        }
                    });
            });
        });
    },
);

const { stopListening: stopSwitchBotDevice, listen: listenSwitchBotDevice } = useEcho(
    `room.${props.room.id}`,
    '.switch_bot_device_data.new',
    (data: any) => {
        Object.keys(data).forEach((key) => {
            (devicesData as any)[key]?.forEach((item: any) => {
                if (data[key].device_id === item.id) {
                    item.latest_data = {
                        ...item.latest_data,
                        ...data[key].context,
                    };
                }
            });
        });
    },
);

const { stopListening: stopSwitchBotRemote, listen: listenSwitchBotRemote } = useEcho(
    `room.${props.room.id}`,
    '.switch_bot_remote_data.new',
    (data: any) => {
        Object.keys(data).forEach((key) => {
            (devicesData as any)[key]?.forEach((item: any) => {
                if (data[key].device_id === item.id) {
                    item.latest_data = {
                        ...item.latest_data,
                        ...data[key].context,
                    };
                }
            });
        });
    },
);

const { stopListening: stopOccupancy, listen: listenOccupancy } = useEcho(`room.${props.room.id}`, '.occupancy.updated', (data: any) => {
    const dailyOcc = devicesData.ai_sensor.find((d: any) => d.name === 'Daily Occupancy') as any;

    if (dailyOcc) {
        dailyOcc.latest_data = [data];
    }
});

const POPUP_MODAL = useModal<Promo[]>();

onMounted(() => {
    if (props.promos && props.promos.length > 0) {
        POPUP_MODAL.open(props.promos);
    }

    listenLorawanDevice();
    listenSwitchBotDevice();
    listenSwitchBotRemote();
    listenOccupancy();
});

onUnmounted(() => {
    stopLorawanDevice();
    stopSwitchBotDevice();
    stopSwitchBotRemote();
    stopOccupancy();
});
</script>

<template>
    <Head :title="$t('head.dashboard')" />
    <UserLayout :title="room.name">
        <UserPageLayout>
            <div class="flex flex-col gap-4 p-2">
                <RoomAreaFilter :area="room.room_areas" :filters="filters" @enableEdit="devicesData.toggleDrag" />
                <ScenesPanel :room />
                <SkeletonLoader v-if="devicesData.isLoading" />
                <GridLayout
                    v-else
                    :dashboardData="{
                        deviceIcon: icon,
                        switch: devicesData.switch,
                        thermostat: devicesData.thermostat,
                        device: devicesData.device,
                        curtain: devicesData.curtain,
                        thermohygrometer: devicesData.thermohygrometer,
                        aircon: devicesData.aircon,
                        projector: devicesData.projector,
                        television: devicesData.television,
                        ai_sensor: devicesData.ai_sensor,
                        blindtilt: devicesData.blindtilt,
                        sensor: devicesData.sensor,
                        occu_temp_light: devicesData.occu_temp_light,
                        co2_temp_humid: devicesData.co2_temp_humid,
                    }"
                />
            </div>
        </UserPageLayout>
    </UserLayout>

    <PromoPopupModal :open="POPUP_MODAL.visible.value && !hidePromo" :promos="POPUP_MODAL.data.value || []" @close="POPUP_MODAL.close()" />
</template>
