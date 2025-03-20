<?php

namespace Xditn\Base\modules\Member\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    /**
     * rules
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                Rule::unique('members')->where(function ($query) {
                    return $query->when($this->get('id'), function ($query) {
                        $query->where('id', '<>', $this->get('id'));
                    })->where('deleted_at', 0);
                }),
            ],

            'mobile' => [
                'required',
                Rule::unique('members')->where(function ($query) {
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

            'mobile.required' => '手机号必须填写',

            'mobile.unique' => '手机号已存在',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => '用户的邮箱地址',
                'required' => true,
                'type' => 'string',
            ],
            'mobile' => [
                'description' => '用户的手机号码',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
