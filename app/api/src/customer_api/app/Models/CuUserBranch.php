<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuUserBranch
 * @property integer $user_branch_id
 * @property integer $user_id
 * @property integer $customer_id
 * @property integer $customer_branch_id
 * @property integer $customer_user_id
 * @property integer $belong
 */
class CuUserBranch extends AppModel
{
    protected $table = 'cu_user_branch';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'user_branch_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_branch_id',
        'user_id',
        'customer_id',
        'customer_branch_id',
        'customer_user_id' ,
        'customer_reminder_sms_flag',
        'create_date',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
        'belong',
        'status'
    ];

    public $timestamps = false;
}

