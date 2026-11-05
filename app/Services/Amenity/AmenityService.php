<?php

namespace App\Services\Amenity;

use App\Models\Amenity;
use App\Models\AmenityCategory;
use App\Models\AmenityPhoto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AmenityService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getAmenities                 (1.0) Retrieve paginated list of amenities.
    // createAmenity                (2.0) Create a new amenity.
    // updateAmenity                (3.0) Update an existing amenity.
    // deleteAmenity                (4.0) Delete an amenity.
    // getAmenityCategories         (5.0) Retrieve all amenity category records.


    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getAmenities
     * <Function> Retrieve paginated list of amenities.
     *
     * @param array $params
     *
     * @return $amenities
     */
    public function getAmenities(array $params)
    {
        [$search, $sort, $direction, $categoryFilter] = [
            @$params['search'],
            @$params['sort'],
            @$params['direction'],
            @$params['categoryFilter']
        ];

        $amenities = Amenity::query()
            ->leftJoin(
                'amenity_categories',
                'amenities.category_id',
                '=',
                'amenity_categories.id'
            )
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('amenities.title', 'like', "%{$search}%")
                        ->orWhere('amenities.description', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($sort, ['title', 'category_id']),
                function ($query) use ($sort, $direction) {
                    if ($sort === 'category_id') {
                        $query->orderBy('amenity_categories.name', $direction);
                    } else {
                        $query->orderBy("amenities.$sort", $direction);
                    }
                }
            )
            ->when($categoryFilter, function ($query, $categoryFilter) {
                $query->whereRaw("LOWER(REPLACE(amenity_categories.name, ' ', '-')) = ?", [strtolower($categoryFilter)]);
            })
            ->with(['amenityCategory', 'photos'])
            ->select('amenities.*') // IMPORTANT: to avoid column collision
            ->latest('amenities.created_at')
            ->paginate(15);

        return $amenities;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createAmenity
     * <Function> Create a new amenity.
     *
     * @param array $amenityData
     *
     * @return $amenity
     */
    public function createAmenity(array $amenityData, array $amenityPhotos)
    {
        return DB::transaction(function () use ($amenityData, $amenityPhotos) {
            // Create amenity
            $amenity = Amenity::create($amenityData);

            // Normalize photos into proper array structure
            $photosArray = collect($amenityPhotos)->map(function ($photo) {
                return is_string($photo)
                    ? ['file_path' => $photo]
                    : $photo;
            })->toArray();

            // Create amenity photos via relationship
            $amenity->photos()->createMany($photosArray);

            return $amenity;
        });
    }


    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateAmenity
     * <Function> Update an existing amenity.
     *
     * @param array $amenityData
     * @param \App\Models\Amenity $amenity
     *
     * @return $amenity
     */
    public function updateAmenity(
        array $amenityData,
        Amenity $amenity,
        array $uploadedNewPhotoPaths,
        array $existingPhotoIds
    ) {
        return DB::transaction(function () use ($amenityData, $amenity, $uploadedNewPhotoPaths, $existingPhotoIds) {
            $amenity->update($amenityData);

            // Delete photos not in existing_photo_ids
            $amenity->photos()
                ->whereNotIn('id', $existingPhotoIds)
                ->each(function ($photo) {

                    $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');
                    $path = parse_url($photo->file_path, PHP_URL_PATH);

                    // Remove leading slash and bucket name
                    $path = ltrim($path, "/{$bucket}/");

                    $relativePath = $path; // "amenity-images/filename.jpg"
                    // Remove file from Google Cloud Storage
                    if (Storage::disk('gcs')->exists($relativePath)) {
                        Storage::disk('gcs')->delete($relativePath);
                    }

                    $photo->delete();
                });

            // Create amenity photos
            foreach ($uploadedNewPhotoPaths as &$newAmenityPhoto) {
                if (is_string($newAmenityPhoto)) {
                    $newAmenityPhoto = ['file_path' => $newAmenityPhoto];
                }

                $newAmenityPhoto['amenity_id'] = $amenity->id;
                $newAmenityPhoto['created_at'] = now();
            }

            // Insert all new photos at once
            if (!empty($uploadedNewPhotoPaths)) {
                $amenity->photos()->createMany($uploadedNewPhotoPaths);
            }
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> deleteAmenity
     * <Function> Delete an amenity.
     *
     * @param
     *
     * @return
     */
    public function deleteAmenity(Amenity $amenity)
    {
        return DB::transaction(function () use ($amenity) {
            // Delete photos from cloud storage
            $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');


            $amenity->photos->each(function ($photo) use ($bucket) {
                // Extract relative path for GCS
                $path = parse_url($photo->file_path, PHP_URL_PATH); // "/tp_dev_bucket/amenity-images/filename.jpg"
                $path = ltrim($path, "/{$bucket}/"); // "amenity-images/filename.jpg"

                $relativePath = $path;

                if (Storage::disk('gcs')->exists($relativePath)) {
                    Storage::disk('gcs')->delete($relativePath);
                }

                $photo->delete();
            });

            // Delete Amenity
            $amenity->delete();
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getAmenityCategories
     * <Function> Retrieve all amenity category records.
     *
     * @return $amenityCategories
     */
    public function getAmenityCategories()
    {
        return AmenityCategory::all();
    }
}
