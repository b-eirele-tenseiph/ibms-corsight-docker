<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize       (1.0) Determine if the user is authorized to make this
    //                       request.
    // rules           (2.0) Get the validation rules that apply to the request.
    // attributes      (3.0) Get custom attributes for validator errors.
    // roleData        (4.0) The role data to be saved to storage.
    // permissionData  (5.0) The permission data to be given to the role.


    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return boolean
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
        $guard = $this->input('guard_name');

        return [
            'name'               => 'required|unique:roles',
            'guard_name'         => 'required',
            'permissions'    => 'required|array|min:1',
            'permissions.*.name' => [
                'required',
                Rule::exists('permissions', 'name')->where(function ($q) use ($guard) {
                    $q->where('guard_name', $guard);
                })
            ],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> attributes
     * <Function> Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'permissions.*.name' => 'permission',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> roleData
     * <Function> The role data to be saved to storage.
     *
     * @return array
     */
    public function roleData(): array
    {

        return [
            'name' => Str::lower($this->name),
            'guard_name' => $this->guard_name
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> permissionData
     * <Function> The permission data to be given to the role.
     *
     * @return array
     */
    public function permissionData(): array
    {
        $permissions = $this->validated()['permissions'];

        $permissions = array_map(function ($permission) {
            return $permission['name'];
        }, $permissions);

        return array_unique($permissions);
    }
}
