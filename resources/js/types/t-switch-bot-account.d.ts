import { QueryParams, Room } from './shared';

type SwitchBotAccountFormData = {
    access_token?: string;
    secret_key?: string;
    webhook_action?: 'store' | 'update';
    webhook_url?: string | null;
};

export interface SwitchBotAccountData extends SwitchBotAccountFormData {
    id: string;
    email: string;
    password: string;
    room: { id: string; name: string; number: number };
    last_synced_at: string | null;
}

export interface SwitchBotAccountProps {
    accounts: PaginatedResponse<SwitchBotAccountData>;
    filters: QueryParams;
    rooms: Array<Room>;
    unlinked_rooms: Array<Room>;
}

export type SwitchBotAccountUpdateFormData = Omit<SwitchBotAccountData, 'room' | 'email' | 'password' | 'last_synced_at'>;

export type SwitchBotAccountCreateFormData = Omit<SwitchBotAccountData, 'id' | 'room' | 'last_synced_at'> & {
    room_id: string;
};

export type SwitchBotAccountShowCredData = Pick<SwitchBotAccountData, 'password' | 'access_token' | 'secret_key'>;
