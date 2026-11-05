export type AmenityPhoto = {
    id: string;
    file_path: string;
    amenity_id: string;
} & Record<string, any>;

export type AmenityPhotoOrFile = AmenityPhoto | File;
