<?php

namespace Xditn\Base\modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;

class SeckillActivityRequest extends Request
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'activity_start_date' => 'required|date',
            'activity_end_date' => 'required|date|gte:activity_start_date',
            'activity_events' => 'required',
            'seckill_product_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_start_date.required' => '秒杀活动开始时间必填',
            'activity_start_date.date' => '秒杀活动开始时间格式不正确',
            'activity_end_date.required' => '秒杀活动结束时间必填',
            'activity_end_date.date' => '秒杀活动结束时间格式不正确',
            'activity_end_date.gte' => '秒杀活动结束时间必须大于等于开始时间',
            'activity_events.required' => '秒杀活动事件必填',
            'seckill_product_id.required' => '秒杀商品必须选择',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'activity_start_date' => [
                'description' => '秒杀活动开始时间',
                'required' => true,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'activity_end_date' => [
                'description' => '秒杀活动结束时间',
                'required' => true,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'activity_events' => [
                'description' => '秒杀活动事件，具体活动内容',
                'required' => true,
                'type' => 'mixed', // 可以根据具体需求定义类型
            ],
            'seckill_product_id' => [
                'description' => '秒杀商品的ID',
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }
}
