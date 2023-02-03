<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class AuthWithBranchRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'required|regex:' . self::REGEX_INT,
            'customer_branch_id' => 'required|regex:' . self::REGEX_INT
        ];
    }

    /**
     * Set validate attribute name
     * @return array
     */
    public function attributes()
    {
        return [
            'customer_id' => __('attributes.cu_user.customer_id'),
            'customer_branch_id' => __('attributes.cu_user.customer_branch_id')
        ];
    }
}
