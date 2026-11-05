import { Room, User } from '.';
import { PaginatedResponse } from './t-paginated-response.d';

export interface RoomAccess {
    id: string;
    room_id: string;
    is_permanent: boolean;
    start_date: string;
    end_date: string;
    start_time: string;
    end_time: string;
}

export type RoomAccessPaginated = PaginatedResponse<RoomAccess>;

export interface RoomAccessWithUser extends RoomAccess {
    user: User;
    room?: Room;
}
