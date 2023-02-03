<?php

namespace App\Rules;

use App\Common\CodeDefinition;

class IsValidPaymentStatus
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
            if (!in_array($item, CodeDefinition::ALL_PAYMENT_STATUS)) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}