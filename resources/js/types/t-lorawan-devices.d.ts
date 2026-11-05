type LorawanDeviceFormData = {
    settings: Record<string, any>;
};

export type LorawanSwitch = BaseDevice;

export interface LorawanDeviceData extends LorawanDeviceFormData {
    id: string;
    name: string;
    dev_eui: string;
    manufacturer_id: number | null;
    model: string | null;
    gateway_id: string | null;
    room_id: string | null;
    room_area_id: string | null;
    context: {
        [key: string]: any;
    };
    online_flag: boolean;
    created_at: string;
    updated_at: string;
    manufacturer?: Manufacturer;
    gateway?: Gateway;
    room?: Room;
    room_area?: RoomArea;
    enable_data_storage?: boolean;
    latest_data?: Array<LorawanDeviceLatestData>;
}

export interface LorawanDeviceLatestData {
    id: number;
    device_id: string;
    type: string;
    context: Record<any, any>;
    created_at: string;
}

export interface LorawanDeviceProps {
    filters: Record<string, any>;
    devices: PaginatedResponse<LorawanDevice>;
    manufacturers: Array<Manufacturer>;
    gateways: Array<Gateway>;
    rooms: Array<Room>;
    roomAreas: Array<RoomArea>;
    models: Array<ManufacturerModel>;
    floors: Array<Floor>;
    title?: string;
}
