<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuSubcontract
 * @property integer $subcontract_id
 * @property integer $customer_id
 * @property string $subcontract_name
 * @property string $subcontract_kana
 * @property string $subcontract_branch_name
 * @property string $subcontract_branch_kana
 * @property string $subcontract_user_division_name
 * @property string $subcontract_user_name
 * @property string $subcontract_user_kana
 * @property string $subcontract_user_email
 * @property string $subcontract_user_tel
 * @property string $subcontract_user_fax
 * @property integer $subcontract_reminder_sms_flag
 */
class CuSubContract extends AppModel
{
    protected $table = 'cu_subcontract';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'subcontract_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'subcontract_id',
        'customer_id',
        'subcontract_name',
        'subcontract_kana',
        'subcontract_branch_name',
        'subcontract_branch_kana',
        'subcontract_branch_tel',
        'subcontract_user_division_name',
        'subcontract_user_name',
        'subcontract_user_kana',
        'subcontract_user_email',
        'subcontract_user_tel',
        'subcontract_user_fax',
        'subcontract_reminder_sms_flag',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type'
    ];
}
