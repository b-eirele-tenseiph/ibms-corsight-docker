import { Room } from '@/types/t-room';
import { RoomAccess } from '@/types/t-room-access';
import { SwitchBotLockAccess } from '@/types/t-switch-bot-lock-access';

export interface AccountManagementUser {
    id: string;
    name: string;
    username: string;
    email: string;
    password: string;
    password_displayable: string;
    room?: Room | null;
    room_access?: RoomAccess | null;
    switch_bot_lock_access?: SwitchBotLockAccess | null;
    hide_promo_popup?: boolean;
    processing: boolean;
}
