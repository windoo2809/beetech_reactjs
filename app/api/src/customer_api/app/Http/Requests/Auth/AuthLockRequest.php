<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class AuthLockRequest extends Request
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
            'login_id' => 'required|max:255|regex:'. self::REGEX_EMAIL
        ];
    }

    /**
     * Set validate attribute name
     * @return array
     */
    public function attributes()
    {
        return [
            'login_id' => __('attributes.cu_user.loginID')
        ];
    }
    /** Customer message error */
    public function messages()
    {
        return [
            'login_id.regex' => __('validation.custom.regex.email'),
        ];
    }
}
