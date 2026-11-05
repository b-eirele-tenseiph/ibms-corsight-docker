import { PaginatedResponse } from './t-paginated-response';

export interface Promo {
    id: string;
    title: string;
    description: string;
    valid_start_date: string;
    valid_end_date: string;
    photo_file_path: string | null;
    photo_file?: File | null;
    created_at: string;
    updated_at: string;
}

export interface PromoProps {
    promos: PaginatedResponse<Promo>;
    queryParams: object;
}

export interface PromoTableProps {
    promos: Record<string, any>;
    onEdit: (promo: Promo) => void;
    onDelete: (promo: Promo) => void;
    queryParams: object;
}

export interface PromoFilterProps {
    queryParams: object;
}

export interface UserPromoProps {
    id: string;
    title: string;
    description: string;
    photo_file_path: string;
}
