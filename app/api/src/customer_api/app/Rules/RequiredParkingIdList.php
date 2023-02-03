<?php

namespace App\Rules;

use App\Dao\DaoConstants;
use App\Http\Middleware\EnsureUserIsValid;
use App\Models\CuParking;
use App\Http\Requests\Request;

class RequiredParkingIdList
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $isRequired = false;
        if (!empty($parameters)) {
            $isRequired = $parameters[0];
        }

        if (!empty($isRequired)) {
            if (empty($value)) {
                return false;
            }
        }

        if (empty($isRequired) && empty($value)) {
            return true;
        }

        if ($value && !is_array($value)) {
            return false;
        }

        $isValid = true;

        foreach ($value as $item) {
            if (!preg_match(Request::REGEX_INT, $item)) {
                $isValid = false;
                break;
            }
        }
        
        if (!$isValid) {
            return false;
        }

        $count = count($value);

        if (!$count) {
            return true;
        }

        $cuParking = CuParking::whereIn('parking_id', $value)->where('status', DaoConstants::STATUS_ACTIVE)->get();
        
        return count($cuParking) == $count;
    }
}