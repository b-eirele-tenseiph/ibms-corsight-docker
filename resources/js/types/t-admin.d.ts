import { PaginatedResponse, Role } from '.';

export interface Admin {
    id: number;
    name: string;
    username: string;
    role_name: string;
    roles: Role[];
    email: string;
    enabled: boolean;
}

export interface AdminProps {
    admins: PaginatedResponse<Admin>;
    queryParams: object;
    roles: Role[];
}

export type AdminForm = Omit<Admin, 'id' | 'enabled' | 'roles' | 'role_name'> & Record<string, any>;
