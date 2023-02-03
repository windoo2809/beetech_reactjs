<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuCustomerOption
 * @property integer    $customer_id
 * @property integer    $core_id
 * @property datetime   $start_date
 * @property datetime   $end_date
 * @property integer    $plan_type
 * @property integer    $admin_user_id
 * @property string     $admin_user_name
 * @property string     $admin_user_login_id
 * @property boolean    $user_lock
 * @property boolean    $approval
 * @property integer    $data_scope
 */
class CuCustomerOption extends AppModel
{
    protected $table = 'cu_customer_option';
    public $timestamps = false;
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'customer_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'core_id',
        'start_date',
        'end_date',
        'plan_type',
        'admin_user_id',
        'admin_user_name',
        'admin_user_login_id',
        'user_lock',
        'approval',
        'data_scope'
    ];
}
