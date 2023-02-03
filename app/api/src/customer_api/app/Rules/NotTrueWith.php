<?php

namespace App\Rules;

class NotTrueWith
{
    /**The values in the two fields cannot be true at the same time. */
    public function validate($attribute, $value, $parameters, $validator)
    {
       
        if (empty($parameters[0])) {
            return false;
        }
        $antherField = $parameters[0];

        if (!empty($value) && !empty(\Request::get($antherField))) {
            return false;
        }

        return true;
    }
}