<?php

namespace App\Rules;

use App\Dao\DaoConstants;
use App\Models\CuParking;
use App\Http\Requests\Request;

class RequiredArrayValue
{
    /**
     * array check
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (empty($value)) {
            return false;
        }

        if (!is_array($value)) {
            return false;
        }

        return count($value) > 0;
    }
}