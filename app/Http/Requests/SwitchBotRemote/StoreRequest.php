<?php

namespace App\Http\Requests\SwitchBotRemote;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize                      (1.0) Determine if the user is authorized
    //                                      to make this request.
    // rules                          (2.0) Get the validation rules that apply
    //                                      to the request.
    // remotesForCreation             (3.0) The newly scanned remotes.
    // remotesForDeletion             (4.0) The outdated remotes on storage.
    // remotesForUpdate               (5.0) The existing remotes without
    //                                      conflict.
    // remotesForDeletionAndCreation  (6.0) The existing remotes with conflict.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'new'                   => 'sometimes|array',
            'outdated'              => 'sometimes|array',
            'updated'               => 'sometimes|array',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> remotesForCreation
     * <Function> The newly scanned remotes.
     *
     * @return array
     */
    public function remotesForCreation()
    {
        // Add additional logic or conditional validation here if any.

        return $this->new;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> remotesForDeletion
     * <Function> The outdated remotes on storage.
     *
     * @return array
     */
    public function remotesForDeletion()
    {
        // Add additional logic or conditional validation here if any.

        return $this->outdated;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> remotesForUpdate
     * <Function> The updated remotes from api.
     *
     * @return array
     */
    public function remotesForUpdate()
    {
        // Add additional logic or conditional validation here if any.

        return $this->updated;
    }
}
