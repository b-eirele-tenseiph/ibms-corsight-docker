import { PaginatedResponse } from './t-paginated-response';
import { Room } from './t-room';

interface Scene {
    id: string;
    context: {
        palette: string;
        icon: string;
    };
    name: string;
    room_id: string;
    scheduled: boolean;
    days: string;
    time: string;
    created_at: string;
    updated_at: string;
    room: Room;
    scene_devices: SceneDevice[];
}
export interface SceneDevice {
    id: string;
    scene_id: string;
    device_type: string;
    device_id: string;
    name: string;
    command: Record<string, any>;
    command_type: string;
    created_at: string;
    updated_at: string;
}

interface LatestData {
    id: number;
    device_type: string;
    deviceId: string;
    context: Record<string, any>;
    receivedAt: string;
    updatedAt: string;
    createdAt: string;
}

interface Device {
    id: string;
    name: string;
    serial_number: string;
    type_id: string;
    hub_id: string;
    enable_data_storage: number;
    created_at: string;
    updated_at: string;
    latest_data: LatestData;
    room_area: any;
    icon: string;
    component: string;
    dev_eui?: string;
    control?: any;
    latest_data?: any;
}

export interface SceneCommandPayload {
    id: string | number;
    command: Record<string, any>;
    type: string;
    remove?: boolean;
}

// Defines the structure for theme palettes.
export interface ThemePalette {
    bg: string;
    text: string;
    border: string;
}

// Defines the structure of the `theme` prop.
export interface Theme {
    palettes: Record<string, ThemePalette>;
    icons: string[];
}

// Defines the structure of the `icon` prop.
export interface IconTheme {
    device_icons: Record<string, string>;
    sensor_icons: Record<string, Record<string, string>>;
}

// Defines the structure of the props for the ScenePage component.
export interface ScenePageProps {
    scenes: PaginatedResponse<Scene>;
    rooms: Room[];
    filters: Record<string, any>;
    theme: Theme;
    icon: IconTheme;
}
