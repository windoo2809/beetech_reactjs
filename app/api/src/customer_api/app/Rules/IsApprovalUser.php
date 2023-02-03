<?php

namespace App\Rules;

use App\Dao\SingleTable\CuUserDao;
use Illuminate\Support\Facades\Auth;

class IsApprovalUser
{
    /**
     * check approval user
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        $user = Auth::user();

        $check = (new CuUserDao())->checkApprove($value, $user->customer_branch_id);
        
        return $check > 0;
    }
}