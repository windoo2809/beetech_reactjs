<?php

namespace App\Rules;

use App\Common\CodeDefinition;

class IsEncodingShiftJIS
{
    /**
     * validate item in array is numeric
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if  (\Request::hasFile($attribute)) {
            $pathReal = \Request::file($attribute)->getRealPath();
            $fileContent = file_get_contents($pathReal);
            if (!mb_detect_encoding($fileContent, [CodeDefinition::SHIFT_JIS_ENCODING, CodeDefinition::SHIFT_JIS_ENCODING_WIN], true) ) {
                return false;
            }
        }
        return true;
    }
}
