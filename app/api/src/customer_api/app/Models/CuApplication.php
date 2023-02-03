<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuCustomerUser
 * @property integer    $application_id
 * @property integer    $estimate_id
 * @property integer    $application_user_id
 * @property datetime   $application_date
 * @property string     $approval_user_id
 * @property datetime   $approval_date
 * @property boolean    $application_status
 * @property string     $application_comment
 * @property string     $approval_comment
 */
class CuApplication extends AppModel
{
    /**
     * @var string
     */
    protected $table = 'cu_application';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'application_id';
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'application_id',
        'estimate_id',
        'application_user_id',
        'application_date',
        'approval_user_id',
        'approval_date',
        'application_status',
        'application_comment',
        'approval_comment',
        'status',
        'update_date',
        'create_user_id',
        'create_system_type',
        "update_user_id",
        "update_system_type",
    ];
}
