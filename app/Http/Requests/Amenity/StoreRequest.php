<?php

namespace App\Http\Requests\Amenity;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to
    //                            make this request.
    // rules                (2.0) Get the validation rules that apply to
    //                            the request.
    // amenityData          (3.0) Return the validated amenity data for
    //                            database.
    // uploadPhotos         (4.0) Uploads amenity photos to Google Cloud Storage
    //                            and returns an array of public URLs
    //                            (or relative paths during tests).
    // failedValidation     (5.0) Handles failed validation by customizing error
    //                            messages for photo uploads before throwing
    //                            a validation exception.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:amenities,title'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'uuid'],
            'photo.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.name' => ['required_with:attributes', 'string', 'max:255'],
            'attributes.*.description' => ['required_with:attributes', 'string', 'max:255'],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> amenityData
     * <Function> Return the validated amenity data for database.
     *
     * @return array
     */
    public function amenityData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'attributes' => $this->attributes,
        ];
    }


    /**
     * <Layer number> (4.0)
     *
     * <Processing name> uploadPhotos
     * <Function> Uploads amenity photos to Google Cloud Storage and returns
     *            an array of public URLs (or relative paths during tests).
     *
     * @return array
     *
     * @throws \Exception If file upload fails.
     */
    public function uploadPhotos(): array
    {
        $paths = [];

        try {
            $files = $this->file('photo');

            if (!$files) {
                return [];
            }

            $files = is_array($files) ? $files : [$files];

            foreach ($files as $photo) {
                // Folder inside bucket
                $amenityFolder = config("filesystems.disks.gcs.amenity_path");

                // Upload to GCS
                $path = Storage::disk('gcs')->put($amenityFolder, $photo);

                if (app()->environment('testing')) {
                    // During tests: store relative path for assertions
                    $paths[] = $path;
                } else {
                    // Production: build full public URL
                    $baseUrl = rtrim(config('filesystems.disks.gcs.storage_api_uri'), '/');
                    $publicUrl = "{$baseUrl}/{$path}";

                    $paths[] = $publicUrl;
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $paths;
    }


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failedValidation
     * <Function> Handles failed validation by customizing error messages for
     *            photo uploads before throwing a validation exception.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        if (collect($errors)->keys()->contains(fn($key) => str_starts_with($key, 'photo.'))) {
            $mergedMessage = __('validation.custom.upload_valid_image');

            $errors['photo'] = [$mergedMessage];

            $errors = array_filter(
                $errors,
                fn($_, $key) => !str_starts_with($key, 'photo.'),
                ARRAY_FILTER_USE_BOTH
            );
        }

        throw ValidationException::withMessages($errors);
    }
}
