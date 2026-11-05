import { PaginatedResponse } from './t-pagination';

export interface Food {
    id: string;
    name: string;
    category_id: number | null;
    category?: FoodCategory;
    description: string;
    price: number;
    image: string;
    available: boolean;
    created_at: string;
    updated_at: string;
}

export interface FoodCategory {
    id: number;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
}

export interface FoodFormPayload {
    name: string;
    category_id: number | null;
    description: string;
    price: number;
    image?: File | string;
    available: boolean;
}

export type SortDirection = 'asc' | 'desc';
export type FoodSortField = 'name' | 'price';

export interface Filters {
    search?: string;
    category?: string | number | null;
    sort?: FoodSortField;
    direction?: SortDirection;
    page?: number;
}

export interface FoodProps {
    rooms: PaginatedResponse<Food>;
}
