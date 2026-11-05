<?php

namespace App\Http\Requests\Promo;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class UpdateRequest extends FormRequest
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to
    //                            make this request.
    // rules                (2.0) Get the validation rules that apply
    //                            to the request.
    // promoData            (3.0) Return the validated promo data for database.
    // uploadPhoto          (4.0) Uploads photo and returns stored path/URL.
    // failedValidation     (5.0) Overrides validation errors.


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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'valid_start_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:valid_end_date'],
            'valid_end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:valid_start_date'],
            'photo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> promoData
     * <Function> Return the validated promo data for database.
     *
     * @return array
     */
    public function promoData()
    {
        return $this->only([
            'title',
            'description',
            'valid_start_date',
            'valid_end_date',
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> uploadPhoto
     * <Function> Uploads photo and returns stored path/URL.
     *
     * @return string
     */
    public function uploadPhoto(): ?string
    {
        if (!$this->hasFile('photo_file')) {
            return null;
        }

        try {
            $file = $this->file('photo_file');
            $promoFolder = config('filesystems.disks.gcs.promo_path');

            $path = Storage::disk('gcs')->put($promoFolder, $file);

            if (app()->environment('testing')) {
                return $path;
            }

            $bucket = config('filesystems.disks.gcs.bucket');
            $baseUrl = rtrim(config('filesystems.disks.gcs.storage_api_uri'), '/');

            return "{$baseUrl}/{$bucket}/{$path}";
        } catch (Exception $e) {
            throw new Exception("Failed to upload promo photo: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failedValidation
     * <Function> Overrides validation errors.
     *
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        if (array_key_exists('photo_file', $errors)) {
            $errors['photo_file'] = [
                __('validation.custom.upload_valid_image')
            ];
        }

        throw ValidationException::withMessages($errors);
    }
}
