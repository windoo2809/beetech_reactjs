<?php

namespace App\Rules;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Log;

class IsListFilePath
{
    /**
     * lis file path check
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
            if (empty($item)) {
                continue;
            }
            
            if (gettype($item) != "string") {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}