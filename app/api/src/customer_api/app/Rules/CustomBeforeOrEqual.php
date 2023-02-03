<?php

namespace App\Rules;

use Carbon\Carbon;

class CustomBeforeOrEqual
{
    /**
     * validate item in array is numeric
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }

        $requestParam = \Request::all();
        if (strpos($parameters[0], '.') !== false) {
            $otherAttr = explode('.', $parameters[0]);
            $targetValue = !empty($requestParam[$otherAttr[0]]) && isset($requestParam[$otherAttr[0]][$otherAttr[1]]) ? $requestParam[$otherAttr[0]][$otherAttr[1]] : NULL;
        } else {
            $targetValue = isset($requestParam[$parameters[0]]) ? $requestParam[$parameters[0]] : null;
        }
        
        $beginTime = strtotime($value);
        $endTime = strtotime($targetValue);
        
        // can be null
        if (!$beginTime || !$endTime) 
        {
            return true;
        }

        if ($beginTime <= $endTime)
        {
            return true;
        }
 
        return false;
    }
}