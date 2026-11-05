import { PaginatedResponse } from './t-pagination';
import type { RoomAccessWithUser } from './t-room-access';

export interface Floor {
    id: number;
    name: string;
    slug?: string;
    used?: boolean;
}

export interface Room {
    id: number | string | null;
    name: string;
    number: string;
    slug: string;
    floor_id: number | null;
    floor?: Floor;
    floor_name?: string;
    room_area?: RoomArea;
    external_apps?: any[];
    status: 'available' | 'check in' | 'check out' | 'occupied' | string;
    room_accesses?: RoomAccessWithUser[];
    created_at: string;
    updated_at: string;
    has_switch_bot_keypad_touch: boolean;
}

export interface RoomForm extends Omit<Room, 'id' | 'floor' | 'floor_id'> {
    id: number | null;
    floor?: Floor;
    floor_id: number | null;
    floor_name?: string;
    [key: string]: any;
}

export interface RoomProps {
    rooms: PaginatedResponse<Room>;
    floors: Floor[];
    queryParams: object;
    external_apps: any[];
}

export interface RoomArea {
    id: string;
    name: string;
    room_id: string;
    created_at: string;
    updated_at: string;
}
