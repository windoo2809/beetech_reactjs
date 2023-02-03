<?php

namespace App\Rules;

class RegexInteger
{
    /**
     * integer check
     */
    public function validate($attribute, $value, $parameters, $validator)
    {

        $len = mb_strlen($value, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $characters = mb_substr($value, $i, 1, 'UTF-8');
            if (!preg_match('/^[0-9]*$/', $characters)) {
                return false;
            }
        }
        return true;
    }
}
