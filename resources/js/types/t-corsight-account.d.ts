import { QueryParams } from './shared';

export interface CorsightAccountData {
    id: string;
    username: string;
    endpoint_url: string | null;
}

export interface CorsightAccountProps {
    accounts: PaginatedResponse<CorsightAccountData>;
    filters: QueryParams;
}

export type CorsightAccountCreateFormData = {
    username: string;
    password: string;
    endpoint_secret: string;
    endpoint_url: string;
};

export type CorsightAccountUpdateFormData = CorsightAccountCreateFormData & {
    id: string;
};

export type CorsightAccountShowCredData = {
    username: string;
    password: string;
    endpoint_secret: string;
    endpoint_url: string;
};
