import { Room } from './shared';

type RemoteRoom = Room & {
    room_areas: {
        [key: string]: any;
    };
};

export interface SwitchBotRemoteData {
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
    enable_data_storage?: boolean;
    latest_data: Record<string, any>;
}

export interface SwitchBotRemoteProps {
    remotes: PaginatedResponse<SwitchBotRemoteData>;
    filters: QueryParams;
    rooms: Array<RemoteRoom>;
}

export type SwitchBotRemoteConfigData = Pick<SwitchBotRemoteData, 'enable_data_storage'>;

export type SwitchBotRemoteSyncData = Pick<SwitchBotRemoteData, 'id' | 'name' | 'serial_number' | 'room'> & {
    switchbot_name?: string;
    status: string;
    type: { id: string };
    hub: { id: string; name: string };
};
