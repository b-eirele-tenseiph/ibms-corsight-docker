import { QueryParams, Room } from './shared';

type DeviceRoom = Room & {
    room_areas: {
        [key: string]: any;
    };
};

export interface CorsightDeviceData {
    id: string;
    name: string;
    role: string;
    enable_data_storage: boolean;
    room: { id: string; name: string };
    room_area: {
        id: string;
        name: string;
        room_id: string;
    };
    health: {
        status: number | null;
        status_str: string | null;
        at: string | null;
        stale: boolean;
    };
}

export interface CorsightDeviceProps {
    devices: PaginatedResponse<CorsightDeviceData>;
    filters: QueryParams;
    rooms: Array<DeviceRoom>;
}

export type CorsightDeviceCreateFormData = {
    name: string;
    role: string;
    room_id: string;
    room_area_id: string;
};

export type CorsightDeviceUpdateFormData = CorsightDeviceCreateFormData & {
    id: string;
};

export type CorsightDeviceConfigData = {
    enable_data_storage: boolean;
};
