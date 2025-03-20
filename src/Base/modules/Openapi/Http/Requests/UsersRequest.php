<?php

namespace Xditn\Base\modules\Openapi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;
use Xditn\Base\modules\Openapi\Models\Users;

class UsersRequest extends Request
{
    protected $stopOnFirstFailure = true;

    /**
     * 规则
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        $rules = [
            'username' => 'required',
            'mobile' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId) {
                    $exist = Users::query()->where('mobile', $value)
                        ->when($userId, function ($query) use ($userId) {
                            return $query->where('id', '!=', $userId);
                        })->exists();

                    if ($exist) {
                        $fail('手机号已存在');
                    }
                },
            ],
        ];

        if ($userId) {
            if ($this->get('password')) {
                $rules['password'] = 'min:6';
            }
        } else {
            $rules['password'] = 'required|min:6';
        }

        return $rules;
    }

    /**
     * 信息
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'username.required' => '请输入用户名',
            'mobile.required' => '请输入手机号',
            'password.required' => '请输入密码',
            'password.min' => '密码不能少于6位',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'username' => [
                'description' => '用户的用户名',
                'required' => true,
                'type' => 'string',
            ],
            'mobile' => [
                'description' => '用户的手机号',
                'required' => true,
                'type' => 'string',
            ],
            'password' => [
                'description' => '用户的密码，修改时可选',
                'required' => !$this->route('user'),
                'type' => 'string',
                'minLength' => 6,
            ],
        ];
    }
}
