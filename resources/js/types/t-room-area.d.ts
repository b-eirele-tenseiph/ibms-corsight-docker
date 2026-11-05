import { Room } from './t-room';

export interface RoomArea {
    id: string;
    room_id: string;
    name: string;
    created_at: string;
    updated_at: string;
    room: Room | null;
}
