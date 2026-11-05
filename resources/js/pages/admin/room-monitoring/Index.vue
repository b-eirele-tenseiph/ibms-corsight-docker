<script setup lang="ts">
import { FloorFilter, RoomFilter, ShowControls } from '@/components/admin/room-monitoring';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useAppearance } from '@/composables/useAppearance';
import { useModal } from '@/composables/useModal';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { airconLoad, curtainLoad, deviceLoad, projectorLoad, switchLoad, televisionLoad, thermostatLoad } from '@/routes/admin/room-monitoring';
import { DeviceAndSensorIcons, Floor, QueryParams, RoomMonitoringDevicesData, RoomMonitoringRoom } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { registerMap } from 'echarts/core';
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue';

const props = defineProps<{
    rooms: Array<RoomMonitoringRoom>;
    query: QueryParams;
    filteredRooms: Array<RoomMonitoringRoom>;
    floors: Array<Floor>;
    icon: DeviceAndSensorIcons;
}>();

const chartOption = ref();

const { visible: showModal, open: openModal, close: closeModal } = useModal();

const roomState = ref<any[]>([]);
const localRooms = ref([...props.rooms]);

const toAreas = (rooms: any[] = []) =>
    rooms.flatMap(({ id: room_id, room_areas = [] }) => {
        return room_areas.map(({ id, name, has_active_device }: any) => {
            return {
                id,
                name,
                room_id,
                value: has_active_device == null ? null : +has_active_device,
            };
        });
    });

const handleChartClick = (e: any) => {
    if (e.seriesType !== 'map' || !e.data?.id) return;
    selectedArea.value = { id: e.data.id, name: e.name };
    selectedRoomId.value = e.data.room_id;

    openModal();
    devicesData.loadControls();
};

const applyChartOptions = (data: any[]) => {
    chartOption.value = {
        visualMap: {
            show: false,
            min: 0,
            max: 1,
            dimension: 0,
            inRange: {
                color: ['#fca5a5', '#bbf7d0'],
            },
        },
        geo: {
            map: 'TPI_Map',
            roam: true,
            zoom: 1.15,
        },
        series: [
            {
                type: 'map',
                geoIndex: 0,
                emphasis: {
                    label: {
                        show: false,
                    },
                },
                selectedMode: false,
                data: data.map((item: any) => ({
                    ...item,
                    itemStyle: {
                        color: item.value === null ? '#e8e6e6' : undefined,
                    },
                })),
            },
        ],
    };
};

const selectedRoomId = ref<string | null>(props.filteredRooms?.[0]?.id ?? null);
const selectedArea = ref<any>(null);
const { appearance } = useAppearance();

const devicesData = reactive<RoomMonitoringDevicesData>({
    isLoading: true,
    switch: [],
    thermostat: [],
    device: [],
    curtain: [],
    blindtilt: [],
    aircon: [],
    projector: [],
    television: [],
    loadControls: async () => {
        devicesData.isLoading = true;
        setTimeout(async () => {
            const load = async (routeName: string, key: keyof typeof devicesData) => {
                try {
                    devicesData[key] = (
                        await axios.get(routeName, {
                            params: { room: selectedRoomId.value, area: selectedArea.value.id },
                        })
                    ).data;
                } catch {}
            };

            await Promise.all([
                load(switchLoad.url(), 'switch'),
                load(thermostatLoad.url(), 'thermostat'),
                load(deviceLoad.url(), 'device'),
                load(curtainLoad.url(), 'curtain'),
                load(airconLoad.url(), 'aircon'),
                load(projectorLoad.url(), 'projector'),
                load(televisionLoad.url(), 'television'),
            ]);

            devicesData.isLoading = false;
        }, 300);
    },
});

watch(
    () => props.filteredRooms,
    (rooms) => (roomState.value = toAreas(rooms ?? [])),
    { immediate: true },
);

watch(roomState, (val) => applyChartOptions(val), { deep: true });

const { stopListening: stopRoomActive, listen: listenRoomActive } = useEcho('admin.room.active', '.active_area.new', (data: any) => {
    const { room_id, room_area_id, device_status } = data;
    const activeIds = Array.isArray(room_area_id) ? new Set(room_area_id) : new Set([room_area_id]);

    roomState.value = roomState.value.map((area: any) => {
        if (area.room_id !== room_id) return area;
        return {
            ...area,
            value: activeIds.has(area.id) ? 1 : 0,
        };
    });

    const room = localRooms.value.find((r: any) => r.id === room_id);
    if (room && room.device_status) {
        Object.assign(room.device_status, device_status);
    }
});

const { stopListening: stopLorawanDevice, listen: listenLorawanDevice } = useEcho(`admin.lorawan_device`, '.lorawan_device_data.new', (data: any) => {
    Object.keys(data).forEach((key) => {
        (devicesData as any)[key]?.forEach((device: any) => {
            (data[key] ?? [])
                .filter((item: any) => item.device_id === device.id)
                .forEach((item: any) => {
                    if (device.latest_data) {
                        device.latest_data[item.type] = {
                            unit: item.context.unit,
                            value: item.context.value,
                        };
                    }
                });
        });
    });
});

const { stopListening: stopSwitchBotDevice, listen: listenSwitchBotDevice } = useEcho(
    `admin.switch_bot_device`,
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
    `admin.switch_bot_remote`,
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

onMounted(() => {
    listenRoomActive();
    listenLorawanDevice();
    listenSwitchBotDevice();
    listenSwitchBotRemote();
});

onMounted(async () => {
    const response = await fetch('/images/Maps/TPI Map.svg');
    const svg = await response.text();
    registerMap('TPI_Map', { svg });

    applyChartOptions(roomState.value);
});

onUnmounted(() => {
    stopRoomActive();
    stopLorawanDevice();
    stopSwitchBotDevice();
    stopSwitchBotRemote();
});
</script>

<template>
    <Head :title="$t('head.room_monitoring')" />
    <AdminLayout title="head.room_monitoring">
        <div class="mx-2 mt-2 mb-1 flex h-full flex-col overflow-hidden md:flex-row">
            <div class="bg-page-background order-2 flex h-1/2 w-full flex-col rounded-xl border py-4 shadow-sm md:order-1 md:h-full md:max-w-xs">
                <div class="overflow-hidden">
                    <FloorFilter class="mb-4 px-4" :query="props.query" :item="props.floors" />
                    <ScrollArea class="h-full px-4">
                        <RoomFilter :query="props.query" :item="localRooms" />
                    </ScrollArea>
                </div>
            </div>
            <div class="order-1 h-1/2 w-full md:h-full lg:order-2">
                <v-chart :theme="appearance" :option="chartOption" @click="handleChartClick" autoresize style="width: 100%; height: 100%" />
            </div>
        </div>
    </AdminLayout>

    <ShowControls
        :isOpen="showModal"
        @close="closeModal"
        :selectedRoom="selectedArea?.name ?? ''"
        :isLoading="devicesData.isLoading"
        :data="{
            deviceIcon: icon,
            switch: devicesData.switch,
            device: devicesData.device,
            aircon: devicesData.aircon,
            thermostat: devicesData.thermostat,
            projector: devicesData.projector,
            television: devicesData.television,
            curtain: devicesData.curtain,
            blindtilt: devicesData.blindtilt,
        }"
    />
</template>
