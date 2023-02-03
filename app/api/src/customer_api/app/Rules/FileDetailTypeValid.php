<?php

namespace App\Rules;

use App\Common\CodeDefinition;

class FileDetailTypeValid
{
    /**
     * check file detail type
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (!isset($value)) {
            return true;
        }

        $fileType = \Request::get('file_type');
        
        if (empty($fileType)) {
            return false;
        }
        
        if (empty(CodeDefinition::FILE_DETAIL_TYPE_GROUP[$fileType])) {
            return false;
        }

        $listFileTypeAllowed = CodeDefinition::FILE_DETAIL_TYPE_GROUP[$fileType];

        return in_array($value, $listFileTypeAllowed);
    }
}