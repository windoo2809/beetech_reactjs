<?php

namespace App\Rules;

use App\Http\Requests\Request;

class NumericItemInArray
{
    /**
     * Array limit check with numbers as parameters.
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (empty($value)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        $isValid = true;

        foreach ($value as $item) {
            if (!preg_match(Request::REGEX_INT, $item)) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}