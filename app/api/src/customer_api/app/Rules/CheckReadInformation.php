<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckReadInformation implements Rule
{
    protected $compareValue;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($compareValue)
    {
        $this->compareValue = $compareValue;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value && !$this->compareValue) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return [
            'message' => __('validation.required_for_read', ['read' => __('attributes.cu_information.read'), 'unread' => __('attributes.cu_information.unread')])
        ];
    }
}
