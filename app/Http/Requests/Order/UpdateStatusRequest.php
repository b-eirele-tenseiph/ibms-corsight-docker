<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // ACTIONS        (1.0) The accepted actions and their status counterpart.
    // authorize      (2.0) Determine if the user is authorized to make this
    //                      request.
    // rules          (3.0) Get the validation rules that apply to the request.
    // newStatus      (4.0) Retrieve the status depending on the action.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> ACTIONS
     * <Function> The accepted actions and their status counterpart.
     *
     * @return array<string,string>
     */
    const ACTIONS = [
        'accept' => 'on cook',
        'complete' => 'completed'
    ];

    /**
     * <Layer number> (2.0)
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
     * <Layer number> (3.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array<string,string>
     */
    public function rules()
    {
        $actions = implode(',', array_keys(self::ACTIONS));

        return [
            'action' => "required|in:{$actions}"
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> newStatus
     * <Function> Retrieve the status depending on the action.
     *
     * @return array<string,string>
     */
    public function newStatus()
    {
        return [
            'status' => self::ACTIONS[$this->action]
        ];
    }
}
