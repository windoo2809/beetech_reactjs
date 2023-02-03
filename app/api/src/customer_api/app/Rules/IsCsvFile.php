<?php

namespace App\Rules;

class IsCsvFile
{
    /**
     * validate item in array is numeric
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if  (\Request::hasFile($attribute)) {
            
            $file = \Request::file($attribute);
            if ($file->getClientMimeType() != "text/csv" ) {
                return false;
            }
        }

        return true;
    }
}