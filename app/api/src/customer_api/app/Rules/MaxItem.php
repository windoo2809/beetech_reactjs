<?php

namespace App\Rules;


use App\Common\CodeDefinition;

class MaxItem
{
    /**
     * validate item in array is numeric
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if  (\Request::hasFile($attribute)) {
            $pathReal = \Request::file($attribute)->getRealPath();
            $fileContent = file_get_contents($pathReal);
            $delimiter = empty($options['delimiter']) ? "," : $options['delimiter'];
            $lines = explode("\r\n", $fileContent);
            $field_names = explode($delimiter, array_shift($lines));
            $maxItemCsvFile = CodeDefinition::MAX_ITEM_CSV_FILE;
            if (count($lines) > $maxItemCsvFile) {
                return false;
            }
        }
        return true;
    }
}
