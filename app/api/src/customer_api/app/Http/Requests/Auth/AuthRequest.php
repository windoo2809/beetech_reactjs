<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class AuthRequest extends Request
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
            'login_id' => 'required',
            'password' => 'required',
            'customer_login_id' => 'required|max:15|regex:' .self::REGEX_CUSTOMER_LOGIN_ID,
        ];
    }

    /**
     * Set validate attribute name
     * @return array
     */
    public function attributes()
    {
        return [
            'login_id' => __('attributes.cu_user.loginID'),
            'password' => __('attributes.cu_user.password'),
            'customer_login_id' => __('attributes.cu_user.customer_login_id'),
        ];
    }

    /**
     * Set message validate custom
     * @return array
     */
    public function messages()
    {
        return [
            'customer_login_id.regex' => __('validation.custom.regex.customer_login_id')
        ];
    }
}
