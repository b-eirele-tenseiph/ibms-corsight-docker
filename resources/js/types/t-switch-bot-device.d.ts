import { Room } from './shared';

type DeviceRoom = Room & {
    room_areas: {
        [key: string]: any;
    };
};

type SwitchBotDeviceFormData = {
    room_area_id?: string | null;
};

export interface SwitchBotDeviceData extends SwitchBotDeviceFormData {
    id: string;
    name: string;
    serial_number: string;
    context: {
        [key: string]: any;
    };
    room: { id: string; name: string };
    room_area: {
        id: string;
        name: string;
        room_id: string;
    };
    type: { id: string; name: string };
    enable_data_storage?: boolean;
    latest_data: Record<string, any>;
}

export interface SwitchBotDeviceProps {
    devices: PaginatedResponse<SwitchBotDeviceData>;
    filters: QueryParams;
    rooms: Array<DeviceRoom>;
}

export type SwitchBotDeviceUpdateFormData = Pick<SwitchBotDeviceData, 'id' | 'name' | 'room_area_id'>;

export type SwitchBotDeviceConfigData = Pick<SwitchBotDeviceData, 'enable_data_storage'>;

export type SwitchBotDeviceSyncData = Pick<SwitchBotDeviceData, 'id' | 'name' | 'serial_number' | 'context' | 'room' | 'type'> & {
    switchbot_name?: string;
    status: string;
    hub: { id: string; name: string };
};
