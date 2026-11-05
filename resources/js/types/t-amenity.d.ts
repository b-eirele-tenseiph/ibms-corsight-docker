import { AmenityAttribute } from './t-amenity-attribute';
import { AmenityCategory } from './t-amenity-category';
import { AmenityPhoto } from './t-amenity-photo';

export interface Amenity {
    id: string;
    title: string;
    description?: string | null;
    is_visible: boolean;
    amenity_category?: AmenityCategory | null;
    photos?: AmenityPhoto[];
    category_id?: string;
    created_at?: string;
    updated_at?: string;
    attributes?: AmenityAttribute[] | null;
}

export type AmenityPhoto = {
    id: string;
    amenity_id: string;
    file_path: string;
};

export type AmenityCategory = {
    id: string;
    name: string;
    slug?: string;
    color?: string | null;
};

export type AmenityAttribute = { name: string; description: string } & Record<string, any>;
