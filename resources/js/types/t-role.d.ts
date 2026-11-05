export interface Paginate<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    first_page_url: string;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export type PaginateWithExtras<T, Extras = Record<string, any>> = Paginate<T> & Extras;

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    updated_at: string;
    permissions: Permission[];
}

export interface AddRoleForm {
    name: string | number;
    guard_name: string | null;
    permissions: { name: string | null; showDropdown: boolean }[];
    [key: string]: any;
}
