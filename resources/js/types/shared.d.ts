export type QueryParams = {
    search?: string | number;
    room?: string;
    area?: string;
    floor?: string;
    direction?: 'asc' | 'desc';
    sort?: string;
    page?: number;
    time?: number;
    start?: Date;
    end?: Date;
    data?: string;
    device_id?: string;
    role?: string;
    date_from?: Date | string ;
    date_to?: Date | string;
    security_only?: boolean | '1';
    in_now?: boolean | '1';
};

export type Appearance = 'light' | 'dark';

export type DateOption = { value: string; label: string };

export type ChartSeries = {
    name: string;
    params?: { min: number; max: number };
    data: [number | string | Date, number][];
};

export type GridPosition = {
    c?: number;
    x: number;
    y: number;
    w: number;
    h: number;
};

export type DeviceAndSensorIcons = {
    device_icons: {
        [key: string]: any;
    };
    sensor_icons: {
        [key: string]: {
            [key: string]: string;
        };
    };
};

export type PalettesAndIcons = {
    palettes: {
        [key: string]: {
            bg: string;
            text: string;
            border: string;
        };
    };
    icons: string[];
};

export interface BaseDevice {
    id: string;
    name: string;
    model?: string;
    type?: string;
    context: {
        [key: string]: any;
    };
    room: { name: string };
    room_area: { name: string };
    latest_data: Record<string, any>;
}

export interface Floor {
    id: number;
    name: string;
    slug?: string;
}

export interface Room {
    id: string;
    name: string;
    number: number;
    floor_id: number;
    slug?: string;
}

export interface RoomArea {
    id: number | string | null;
    name: string;
    room_id?: string;
    slug?: string;
}

export interface ManufacturerModel {
    id: number;
    name: string;
}

export interface Manufacturer {
    id: number;
    name: string;
    models: Array<ManufacturerModel>;
}

export interface Gateway {
    id: string;
    ip_address: string;
}
