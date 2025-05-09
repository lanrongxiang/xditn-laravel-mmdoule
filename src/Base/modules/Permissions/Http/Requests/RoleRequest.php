<?php

namespace Xditn\Base\modules\Permissions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * rules
     */
    public function rules(): array
    {
        return [
            'role_name' => [
                'required',
                Rule::unique('roles')->where(function ($query) {
                    return $query->when($this->get('id'), function ($query) {
                        $query->where('id', '<>', $this->get('id'));
                    })->where('deleted_at', 0);
                }),
            ],

            'identify' => [
                'required',
                'alpha',
                Rule::unique('roles')->where(function ($query) {
                    return $query->when($this->get('id'), function ($query) {
                        $query->where('id', '<>', $this->get('id'));
                    })->where('deleted_at', 0);
                }),
            ],
        ];
    }

    /**
     * messages
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'role_name.required' => '角色名称必须填写',

            'role_name.unique' => '角色名称已存在',

            'identify.required' => '角色标识必须填写',

            'identify.alpha' => '角色标识只允许字母组成',

            'identify.unique' => '角色标识已存在',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'role_name' => [
                'description' => '角色名称',
                'required' => true,
                'type' => 'string',
            ],
            'identify' => [
                'description' => '角色标识',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
