<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuCustomerUser
 * @property integer    $customer_user_id
 * @property integer    $customer_id
 * @property integer    $customer_branch_id
 * @property integer    $core_id
 * @property string     $customer_user_name
 * @property string     $customer_user_name_kana
 * @property string     $customer_user_division_name
 * @property string     $customer_user_email
 * @property string     $customer_user_tel
 * @property boolean    $customer_reminder_sms_flag
 */
class CuCustomerUser extends AppModel
{
    protected $table = 'cu_customer_user';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'customer_user_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_user_id',
        'customer_id',
        'customer_branch_id',
        'core_id',
        'customer_user_name',
        'customer_user_name_kana',
        'customer_user_division_name',
        'customer_user_email',
        'customer_user_tel',
        'customer_reminder_sms_flag',
        'create_date',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
        'customer_reminder_sms_flag',
        'status'
    ];
}

