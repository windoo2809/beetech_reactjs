<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class CuUser
 * @property integer $user_id
 * @property integer $customer_id
 * @property string  $login_id
 * @property string  $password
 * @property boolean $user_lock
 * @property boolean $access_flg
 * @property integer $role
 * @property string $customer_user_name
 * @property string $customer_user_name_kana
 * @property boolean $customer_reminder_sms_flag
 * @property string $customer_user_tel
 */
class CuUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'cu_user';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'login_id',
        'password',
        'user_lock',
        'access_flg',
        'role',
        'customer_user_name',
        'customer_user_name_kana',
        'customer_reminder_sms_flag',
        'customer_user_tel',
        'change_login_id',
        'update_date',
        'update_user_id',
        'update_system_type',
        'create_date',
        'create_user_id',
        'create_system_type',
        'status',
    ];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    const CREATED_AT = 'create_date';
    
    const UPDATED_AT = 'update_date';

    public $timestamps = false;
}
