<?php

namespace App\Http\Requests\SwitchBotHub;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize                   (1.0) Determine if the user is authorized
    //                                   to make this request.
    // rules                       (2.0) Get the validation rules that apply
    //                                   to the request.
    // hubsForCreation             (3.0) The newly scanned hubs.
    // hubsForDeletion             (4.0) The outdated hubs on storage.
    // hubsForUpdate               (5.0) The updated hubs from api.

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
            'new'                  => 'sometimes|array',
            'outdated'             => 'sometimes|array',
            'updated'              => 'sometimes|array',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> hubsForCreation
     * <Function> The newly scanned devices.
     *
     * @return array
     */
    public function hubsForCreation(): array
    {
        return collect($this->new)->map(function ($item) {
            return $item;
        })->toArray();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> hubsForDeletion
     * <Function> The outdated devices on storage.
     *
     * @return mixed
     */
    public function hubsForDeletion(): mixed
    {
        // Add additional logic or conditional validation here if any.
        return $this->outdated;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> hubsForUpdate
     * <Function> The updated devices from api
     *
     * @return array
     */
    public function hubsForUpdate(): array
    {
        // Add additional logic or conditional validation here if any.
        return collect($this->updated)->map(function ($item) {
            return $item;
        })->toArray();
    }
}
