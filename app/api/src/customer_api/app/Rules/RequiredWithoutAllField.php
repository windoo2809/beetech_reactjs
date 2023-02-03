<?php

namespace App\Rules;

use App\Common\CodeDefinition;

class RequiredWithoutAllField
{
    /**
     * check required without all
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        array_push($parameters, $attribute);
        
        $request = \Request::all();

        foreach($parameters as $item) {
            if (isset($request[$item])){
                return true;
            }
        }
         
        return false;
    }
}