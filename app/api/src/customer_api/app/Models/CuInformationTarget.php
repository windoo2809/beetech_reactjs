<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuInformationTarget
 * @property integer $information_target_id
 * @property integer $information_id
 * @property integer $customer_id
 * @property integer $customer_branch_id
 * @property integer $customer_user_id
 */
class CuInformationTarget extends AppModel
{
    //
    protected $table = 'cu_information_target';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'information_target_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'information_target_id',
        'information_id',
        'customer_id',
        'customer_branch_id',
        'customer_user_id'
    ];
}
