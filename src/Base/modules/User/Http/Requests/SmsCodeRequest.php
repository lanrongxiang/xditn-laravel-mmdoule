<?php

namespace Xditn\Base\modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsCodeRequest extends FormRequest
{
    /**
     * rules
     */
    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! preg_match('/^1\d{10}$/', $value)) {
                        $fail('手机格式不正确');
                    }
                },
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
            'mobile.required' => '手机号码未填写',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'mobile' => [
                'description' => '手机号码',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
