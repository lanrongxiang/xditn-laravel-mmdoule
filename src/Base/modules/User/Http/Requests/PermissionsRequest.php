<?php

namespace Xditn\Base\modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as Request;

class PermissionsRequest extends Request
{
    protected $stopOnFirstFailure = true;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|numeric',

        ];
    }


    /**
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
