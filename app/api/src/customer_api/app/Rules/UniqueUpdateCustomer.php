<?php

namespace App\Rules;

use App\Models\CuUser;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UniqueUpdateCustomer
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $cuUserSuper = CuUser::where([
            ['login_id', $value],
            ['user_id', "<>", request()->user_id],
        ])->first();
        if ($cuUserSuper) {
            return false;
        }
        return true;
    }
}
