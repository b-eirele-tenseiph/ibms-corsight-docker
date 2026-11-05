<?php

namespace App\Http\Requests\Promo;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to
    //                            make this request.
    // rules                (2.0) Get the validation rules that apply
    //                            to the request.
    // promoData            (3.0) Return the validated promo data for database.
    // uploadPhoto          (4.0) Uploads the promo photo file to GCS and
    //                            returns the stored path.
    // failedValidation     (5.0) Overrides the default failed validation
    //                            handler to provide a custom error message
    //                            for photo file validation if dates match.

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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'valid_start_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:valid_end_date'],
            'valid_end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:valid_start_date'],
            'photo_file' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
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
    public function promoData(): array
    {
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'valid_start_date' => $this->valid_start_date,
            'valid_end_date' => $this->valid_end_date,
        ];

        if ($this->hasFile('photo_file')) {
            $data['photo_file_path'] = $this->uploadPhoto();
        }

        return $data;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> uploadPhoto
     * <Function> Uploads the promo photo file to GCS and returns the stored path.
     *
     * @return string
     */
    protected function uploadPhoto()
    {
        try {
            $photo = $this->file('photo_file');
            $promoFolder = config('filesystems.disks.gcs.promo_path');

            // Upload to GCS
            $path = Storage::disk('gcs')->put($promoFolder, $photo);

            if (app()->environment('testing')) {
                return $path;
            }

            $baseUrl = rtrim(config('filesystems.disks.gcs.storage_api_uri'), '/');

            return "{$baseUrl}/{$path}";
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failedValidation
     * <Function> Overrides the default failed validation handler to provide
     *            a custom error message for photo file validation.
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
