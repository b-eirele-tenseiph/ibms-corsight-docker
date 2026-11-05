<?php

namespace App\Services\Promo;

use App\Models\Promo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromoService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getPromos                     (1.0) Retrieve paginated list of
    //                                     promo records.
    // createPromo                   (2.0) Create a new promo record.
    // updatePromo                   (3.0) Update an existing promo record.
    // deletePromo                   (4.0) Delete a promo record.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getPromos
     * <Function> Retrieve paginated list of promo records.
     *
     * @param array $params
     *
     * @return $promos
     */
    public function getPromos(array $params)
    {
        [$search, $sort, $direction] = [
            @$params['search'],
            @$params['sort'],
            @$params['direction']
        ];

        $promos = Promo::query()
            ->when(
                $search,
                function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                in_array($sort, ['title', 'valid_start_date']),
                function ($query) use ($sort, $direction) {
                    $query->orderBy($sort, $direction);
                }
            )
            ->latest()
            ->paginate(15);

        return $promos;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createPromo
     * <Function> Update an existing promo record.
     *
     * @param array $promoData
     *
     * @return \App\Models\Promo
     */
    public function createPromo(array $promoData)
    {
        return DB::transaction(function () use ($promoData) {
            // Create promo
            $promo = Promo::create($promoData);

            return $promo;
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updatePromo
     * <Function> Update an existing promo record.
     *
     * @param array $promoData
     * @param \App\Models\Promo $promo
     * @param string|null $photoFile
     *
     * @return \App\Models\Promo
     */
    public function updatePromo(
        array $promoData,
        Promo $promo,
        ?string $photoFile = null
    ) {
        return DB::transaction(function () use ($promoData, $promo, $photoFile) {

            // If a new photo is uploaded
            if ($photoFile) {
                if ($promo->photo_file_path) {
                    $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');
                    $urlPath = parse_url($promo->photo_file_path, PHP_URL_PATH);

                    // Remove the bucket part from the URL path
                    $path = Str::replaceFirst("/{$bucket}/", '', $urlPath);

                    if (Storage::disk('gcs')->exists($path)) {
                        Storage::disk('gcs')->delete($path);
                    }
                }

                // Save new photo path
                $promoData['photo_file_path'] = $photoFile;
            }

            $promo->update($promoData);

            return $promo;
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> deletePromo
     * <Function> Delete a promo record.
     *
     * @param \App\Models\Promo $promo
     *
     * @return void
     */
    public function deletePromo(Promo $promo)
    {
        return DB::transaction(function () use ($promo) {
            // Delete photo from cloud storage
            $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');

            $urlPath = parse_url($promo->photo_file_path, PHP_URL_PATH);

            $path = Str::replaceFirst("/{$bucket}/", '', $urlPath);

            if (Storage::disk('gcs')->exists($path)) {
                Storage::disk('gcs')->delete($path);
            }

            // Delete promo
            $promo->delete();
        });
    }
}
