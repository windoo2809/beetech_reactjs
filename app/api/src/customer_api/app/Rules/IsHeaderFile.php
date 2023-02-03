<?php

namespace App\Rules;


use App\Common\CodeDefinition;

class IsHeaderFile
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
            $headerCsvFile = CodeDefinition::HEADER_CSV_FILE;
            if (!is_array($field_names)
                || count($field_names) != count($headerCsvFile)
                || $headerCsvFile != $field_names) {
                return false;
            }
        }
        return true;
    }
}
