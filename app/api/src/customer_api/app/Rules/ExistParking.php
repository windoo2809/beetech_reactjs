<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Dao\DaoConstants;
use App\Models\CuParking;

class ExistParking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        if (!is_array($value)) {
            return false;
        }

        $count = count($value);

        if (!$count) {
            return true;
        }

        $cuParking = CuParking::whereIn('parking_id', $value)->where('status', DaoConstants::STATUS_ACTIVE)->get();
        
        return count($cuParking) == $count;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.parking_id.exist_in_database');
    }
}
