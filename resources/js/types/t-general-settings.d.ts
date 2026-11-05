export type Rate = {
    effective_date: string | null;
    rate_per_kwh: number;
};

export type RateHistory = Rate & {
    id: string;
    latest: boolean;
};

export interface FileProps {
    files: PaginatedResponse<FileData>;
    filters: QueryParams;
    isArchive?: boolean;
}

export interface FileData {
    id: string;
    name: string;
    extension: string;
    size: string;
    path: string;
    admin_id: string;
    created_at: string;
    days_left: number | null;
    admin: {
        id: string;
        name: string;
        role_name: string;
        roles: Array<Record<string, any>>;
    };
}
