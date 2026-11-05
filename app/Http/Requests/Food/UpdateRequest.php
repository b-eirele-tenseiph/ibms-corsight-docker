<?php

namespace App\Http\Requests\Food;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize    (1.0) Determine if the user is authorized to make this
    //                    request.
    // rules        (2.0) Get the validation rules that apply to the request.
    // foodData     (3.0) Get validated food data
    // uploadImage  (4.0) Upload image to google cloud storage

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize()
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
    public function rules()
    {
        return [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|integer|exists:food_categories,id',
            'description' => 'required|string',
            'price'       => 'required|numeric|gt:0|lt:9999',
            'available'   => 'required|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> foodData
     * <Function> Get validated food data
     *
     * @return array
     */
    public function foodData()
    {
        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'available' => $this->available
        ];

        if ($this->image) {
            $data['image'] = $this->uploadImage();
        }

        return $data;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> uploadImage
     * <Function> Upload image to google cloud storage
     *
     * @return string
     */
    public function uploadImage()
    {
        if ($this->hasFile('image')) {
            try {
                $photo = $this->file('image');
                // Folder inside bucket
                $foodFolder = config("filesystems.disks.gcs.food_path");

                // Upload to GCS
                $path = Storage::disk('gcs')->put($foodFolder, $photo);

                return Storage::disk('gcs')->url($path);
            } catch (\Exception $e) {
                // Catch any exceptions thrown during the file upload process
                throw ValidationException::withMessages([
                    'image' => 'The image failed to upload. Please try again.',
                ]);
            }
        }
    }
}
