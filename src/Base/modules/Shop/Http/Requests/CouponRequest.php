<?php

namespace Xditn\Base\modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;
use Xditn\Base\modules\Shop\Enums\CouponExpireType;
use Xditn\Base\modules\Shop\Enums\CouponScope;
use Xditn\Base\modules\Shop\Enums\CouponType;

class CouponRequest extends Request
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'title' => 'required|min:2,max:255',
            'type' => 'required',
            'reduce_price' => sprintf('required_if:type,%d|min:0', CouponType::FULL_REDUCE->value()),
            'discount' => sprintf('required_if:type,%d|min:0|max:9.9', CouponType::DISCOUNT->value()),
            'min_price' => 'required|min:0',
            'expire_type' => 'required',
            'validaty' => sprintf('exclude_if:expire_type,%d|required_if:expire_type,%d|numeric|min:1', CouponExpireType::FIX_TIME->value(), CouponExpireType::RECEIVE_EFFECT->value()),
            'start_at' => sprintf('exclude_if:expire_type,%d|required_if:expire_type,%d|date', CouponExpireType::RECEIVE_EFFECT->value(), CouponExpireType::FIX_TIME->value()),
            'end_at' => sprintf('exclude_if:expire_type,%d|required_if:expire_type,%d|date|after:start_at', CouponExpireType::RECEIVE_EFFECT->value(), CouponExpireType::FIX_TIME->value()),
            'scope' => 'required',
            'scope_data' => sprintf('exclude_if:expire_type,%d|required_if:scope,%d|array', CouponScope::FULL->value(), CouponScope::PRODUCTS->value()),
            'total_num' => 'required|numeric|min:0',
            'status' => 'required|min:1',
            'sort' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '优惠券标题必填',
            'title.min' => '优惠券标题最少2个字符',
            'title.max' => '优惠券标题最多255个字符',
            'type.required' => '优惠券类型必填',
            'reduce_price.required_if' => '满减金额必填',
            'reduce_price.min' => '满减金额不能小于0',
            'discount.required_if' => '优惠券折扣率必填',
            'discount.min' => '优惠券折扣率不能小于0',
            'discount.max' => '优惠券折扣率不能大于9.9',
            'min_price.required' => '最低消费金额必填',
            'min_price.min' => '最低消费金额不能小于0',
            'expire_type.required' => '到期类型必填',
            'validaty.required_if' => '优惠券有效期必填',
            'validaty.numeric' => '优惠券有效期必须是数字',
            'validaty.min' => '优惠券有效期不能小于1天',
            'start_at.required_if' => '优惠券领取开始时间必填',
            'start_at.date' => '优惠券领取开始时间格式不正确',
            'end_at.required_if' => '优惠券领取结束时间必填',
            'end_at.date' => '优惠券领取结束时间格式不正确',
            'end_at.after' => '优惠券领取结束时间必须大于开始时间',
            'scope.required' => '优惠券适用范围必填',
            'scope_data.required_if' => '必须选择指定商品',
            'total_num.required' => '优惠券发放总数量必填',
            'total_num.numeric' => '优惠券发放总数量必须是数字',
            'total_num.min' => '优惠券发放总数量不能小于0',
            'status.required' => '优惠券状态必填',
            'sort.required' => '优惠券排序必填',
            'sort.numeric' => '优惠券排序必须是数字',
            'sort.min' => '优惠券排序不能小于1',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => '优惠券标题',
                'required' => true,
                'type' => 'string',
            ],
            'type' => [
                'description' => '优惠券类型',
                'required' => true,
                'type' => 'integer',
            ],
            'reduce_price' => [
                'description' => '满减金额，适用于满减类型的优惠券',
                'required' => false,
                'type' => 'float',
            ],
            'discount' => [
                'description' => '优惠券折扣率，适用于折扣类型的优惠券',
                'required' => false,
                'type' => 'float',
            ],
            'min_price' => [
                'description' => '最低消费金额',
                'required' => true,
                'type' => 'float',
            ],
            'expire_type' => [
                'description' => '优惠券到期类型',
                'required' => true,
                'type' => 'integer',
            ],
            'validaty' => [
                'description' => '优惠券有效期，单位为天',
                'required' => false,
                'type' => 'integer',
            ],
            'start_at' => [
                'description' => '优惠券领取开始时间',
                'required' => false,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'end_at' => [
                'description' => '优惠券领取结束时间',
                'required' => false,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'scope' => [
                'description' => '优惠券适用范围',
                'required' => true,
                'type' => 'integer',
            ],
            'scope_data' => [
                'description' => '适用范围数据，若适用范围为指定商品，则此参数为商品ID数组',
                'required' => false,
                'type' => 'array',
            ],
            'total_num' => [
                'description' => '优惠券发放总数量',
                'required' => true,
                'type' => 'integer',
            ],
            'status' => [
                'description' => '优惠券状态',
                'required' => true,
                'type' => 'integer',
            ],
            'sort' => [
                'description' => '优惠券排序值',
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }
}
