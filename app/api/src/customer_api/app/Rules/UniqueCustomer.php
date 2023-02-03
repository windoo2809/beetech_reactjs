<?php

namespace App\Rules;

use App\Models\CuUser;
use Illuminate\Support\Facades\Auth;

class UniqueCustomer
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $res = CuUser::where([
            'login_id' => $value,
            'customer_id' => Auth::user()->customer_id,
        ])->first();
        if (!$res) {
            return true;
        }
        return false;
    }
}
