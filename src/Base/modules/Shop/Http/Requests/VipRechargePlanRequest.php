<?php

namespace Xditn\Base\modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;

class VipRechargePlanRequest extends Request
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'title' => 'required|min:2,max:255',
            'price' => 'required|min:0',
            'handsel_price' => 'required|min:0|lt:price',
            'sort' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '会员充值标题必填',
            'title.min' => '会员充值标题最少2个字符',
            'title.max' => '会员充值标题最多255个字符',
            'price.required' => '会员充值金额必填',
            'price.min' => '会员充值金额不能小于0',
            'handsel_price.required' => '赠送金额必填',
            'handsel_price.min' => '会员赠送金额不能小于0',
            'handsel_price.lt' => '会员赠送金额不能大于充值金额',
            'sort.required' => '排序必填',
            'sort.numeric' => '排序必须是数字',
            'sort.min' => '排序不能小于1',
        ];
    }
}
