<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        return [
            'name' => [
                'required',
                Rule::unique('roles', 'name')->ignore($request->route('role')?->id),
            ],
            'guard_name'     => 'nullable|in:admin,web',
            'permissions'    => 'required|array|min:1',
            'permissions.*'  => 'required|integer|exists:permissions,id',
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
        return array_unique($this->validated()['permissions'] ?? []);
    }
}
