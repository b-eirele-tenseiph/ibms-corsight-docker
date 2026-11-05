import { BaseDevice, DeviceAndSensorIcons, GridPosition, Room, RoomArea } from './shared';

interface TimeSeriesDevice extends Omit<BaseDevice, 'latest_data'> {
    series: Array<ChartSeries>;
}

interface SensorDevice extends BaseDevice {
    room: { name: string; room_area?: string };
    latest_data: Record<string, any> | any[];
}

interface ControlDevice extends BaseDevice {
    positions?: {
        id: number;
        room_area: string;
        context?: Record<string, any>;
    }[];
}

export type UserDashboardRoom = Room & {
    room_areas: Array<RoomArea>;
};

export type UserGridPositionLayout = GridPosition & {
    id: string | number;
    room_area: string;
    type: string;
    column: number;
};

export type UserGridPositionLayoutItem = {
    position?: GridPosition;
    type: string;
    isLocked?: boolean;
    data: { id: string | number; type?: string; model?: string };
};

export interface SensorDataItem {
    label: string;
    value: string | number;
    unit?: string;
    color?: { light: string; dark: string };
}

export interface DashboardUserData {
    deviceIcon?: DeviceAndSensorIcons;
    switch: Array<ControlDevice>;
    thermostat: Array<ControlDevice>;
    device: Array<ControlDevice>;
    sensor: Array<SensorDevice>;
    curtain: Array<ControlDevice>;
    blindtilt: Array<ControlDevice>;
    aircon: Array<ControlDevice>;
    projector: Array<ControlDevice>;
    television: Array<ControlDevice>;
    ai_sensor: Array<SensorDevice>;
    occu_temp_light: Array<TimeSeriesDevice>;
    co2_temp_humid: Array<TimeSeriesDevice>;
    thermohygrometer: Array<TimeSeriesDevice>;
}

export interface DashboardUserDevicesData extends Omit<DashboardUserData, 'deviceIcon'> {
    isLoading: boolean;
    dragEnabled: boolean;
    isDirty: boolean;
    currentArea: ReturnType<typeof ref<string>>;
    toggleDrag: () => void;
    loadControls: () => Promise<void>;
}
