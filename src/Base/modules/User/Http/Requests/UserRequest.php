<?php

namespace Xditn\Base\modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * rules
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                Rule::unique('admin_users')->where(function ($query) {
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
            'email.required' => '邮箱必须填写',

            'email.unique' => '邮箱已存在',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => '用户的电子邮箱地址',
                'required' => true,
                'type' => 'string',
            ],
            'id' => [
                'description' => '用户的唯一标识符，更新时需要',
                'required' => false,
                'type' => 'integer',
            ],
        ];
    }
}
