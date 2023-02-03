<?php

namespace App\Rules;

use App\Dao\DaoConstants;


class CustomRequiredWithout
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $request = \Request::all();

        if (empty($value) && empty($request[$parameters[0]])){
            return false;
        }

        return true;
    }
}