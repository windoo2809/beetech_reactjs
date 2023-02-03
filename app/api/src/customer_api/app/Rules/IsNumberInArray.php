<?php

namespace App\Rules;

class IsNumberInArray
{
    /**
     * パラメータが数字の配列制限チェック。
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
            if (!is_numeric($item)) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}