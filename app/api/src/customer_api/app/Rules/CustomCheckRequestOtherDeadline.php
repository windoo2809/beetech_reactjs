<?php

namespace App\Rules;

use App\Common\CodeDefinition;
use Carbon\Carbon;

class CustomCheckRequestOtherDeadline
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
        
        $requestData = $requestParam['request_data'];
        
        if (empty($requestData)) {
            return true;
        }

        if (isset($requestData['request_type']) && $requestData['request_type'] == CodeDefinition::REQ_EXTEND) {
            return true;
        }

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