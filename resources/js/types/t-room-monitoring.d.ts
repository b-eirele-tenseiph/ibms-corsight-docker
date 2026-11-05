import { BaseDevice, Room, RoomArea } from './shared';

interface ControlDevice extends BaseDevice {
    positions?: {
        id: number;
        room_area: string;
        context?: Record<string, any>;
    }[];
}

export interface RoomMonitoringData {
    deviceIcon?: DeviceAndSensorIcons;
    switch: Array<ControlDevice>;
    thermostat: Array<ControlDevice>;
    device: Array<ControlDevice>;
    curtain: Array<ControlDevice>;
    blindtilt: Array<ControlDevice>;
    aircon: Array<ControlDevice>;
    projector: Array<ControlDevice>;
    television: Array<ControlDevice>;
}

export type RoomMonitoringRoom = Omit<Room, 'floor_id'> & {
    device_status?: { active: number; total: number };
    room_areas: RoomArea & {
        has_active_device: boolean;
    };
};

export interface RoomMonitoringDevicesData extends Omit<RoomMonitoringData, 'deviceIcon'> {
    isLoading: boolean;
    loadControls: () => Promise<void>;
}

export interface ControlsDeviceList {
    id: string;
    type: string;
    title: string;
    component: any;
    indicators: Array<boolean>;
    props: {
        [key: string]: any;
    };
}
