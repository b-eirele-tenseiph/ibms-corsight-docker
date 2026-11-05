import { devices as routeDevice } from '@/routes/scenes';
import axios from 'axios';
import { ref, watch, type Ref } from 'vue';

const DEVICE_COMPONENT_MAP: Record<string, string> = {
    aircons: 'AirconCard',
    plugs: 'ControlCard',
    bots: 'ControlCard',
    curtains: 'CurtainCard',
    blinds: 'BlindTiltCard',
    thermostats: 'ThermostatCard',
    televisions: 'TelevisionCard',
    projectors: 'ProjectorCard',
    switches: 'SwitchCard',
    locks: 'ControlCard',
};

const DEVICE_ICON_MAP: Record<string, string> = {
    aircons: 'mynaui:air-conditioner',
    plugs: 'material-symbols:outlet-outline',
    bots: 'material-symbols:switch-outline-rounded',
    curtains: 'mingcute:curtain-line',
    blinds: 'mdi:window-shutter',
    thermostats: 'mynaui:air-conditioner',
    televisions: 'ph:television',
    projectors: 'bi:projector',
    switches: 'mdi:lightbulb-outline',
    locks: 'material-symbols:lock-outline',
};

export function useRoomDevices(roomId: Ref<string | null>) {
    const devices = ref<any[]>([]);
    const isLoading = ref(false);

    const fetchDevices = async (id: string | null) => {
        if (!id) {
            devices.value = [];
            return;
        }

        isLoading.value = true;
        try {
            const { data } = await axios.get(routeDevice.url(), {
                params: { room_id: id, require_latest: true },
            });

            const combinedList = Object.entries(data).flatMap(([deviceType, deviceList]) => {
                const component = DEVICE_COMPONENT_MAP[deviceType];
                const icon = DEVICE_ICON_MAP[deviceType];
                if (!component || !Array.isArray(deviceList)) return [];

                return deviceList.map((d: any) => ({
                    ...d,
                    component,
                    icon,
                    control: d.latest_data?.context ?? {},
                }));
            });

            devices.value = Object.values(combinedList.reduce((acc, it) => ((acc[it.id] = it), acc), {}));
        } catch (e) {
            console.error('Failed to load devices:', e);
            devices.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    watch(roomId, (newRoomId) => fetchDevices(newRoomId), { immediate: true });

    return { devices, isLoading };
}
