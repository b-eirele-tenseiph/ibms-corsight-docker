import { Room, RoomArea } from './shared';

type HubRoom = Room & {
    room_areas: {
        [key: string]: any;
    };
};

type SwitchBotHubFormData = {
    room_area_id?: string | null;
};

export interface SwitchBotHubData extends SwitchBotHubFormData {
    id: string;
    name: string;
    serial_number: string;
    room: Room;
    room_area: RoomArea;
    status: string;
    type_id: string;
    switchbot_hub: string;
}

export interface SwitchBotHubProps {
    hubs: PaginatedResponse<SwitchBotHubData>;
    filters: QueryParams;
    rooms: Array<HubRoom>;
}
