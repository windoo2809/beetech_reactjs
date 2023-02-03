<?php

namespace App\Models;

use App\Models\Model as AppModel;
use Psy\Context;

/**
 * Class CuRequest
 * @property integer $request_id
 * @property integer $project_id
 * @property datetime $request_date
 * @property datetime $estimate_deadline
 * @property integer $request_type
 * @property datetime $want_start_date
 * @property datetime  $want_end_date
 * @property integer  $car_qty
 * @property integer  $light_truck_qty
 * @property integer  $truck_qty
 * @property integer  $other_car_qty
 * @property Context  $other_car_detail
 * @property integer  $want_guide_type
 * @property string   $cc_email
 * @property decimal  $response_request_date
 * @property Context  $customer_other_request
 * @property integer  $request_other_qty
 * @property integer  $request_status
 * @property integer  $subcontract_want_guide_type
 * @property string   $subcontract_name
 * @property string   $subcontract_kana
 * @property string   $subcontract_branch_name
 * @property string   $subcontract_branch_kana
 * @property string   $subcontract_user_division_name
 * @property string   $subcontract_user_name
 * @property string   $subcontract_user_kana
 * @property string   $subcontract_user_email
 * @property string   $subcontract_user_tel
 * @property string   $subcontract_user_fax
 * @property boolean  $subcontract_reminder_sms_flag
 */
class CuRequest extends AppModel
{
    //
    protected $table = 'cu_request';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'request_id',
        'project_id',
        'core_id',
        'request_date',
        'estimate_deadline',
        'request_type',
        'want_start_date',
        'want_end_date',
        'car_qty',
        'light_truck_qty',
        'truck_qty',
        'other_car_qty',
        'other_car_detail',
        "send_destination_type",
        'want_guide_type',
        'cc_email',
        'response_request_date',
        'customer_other_request',
        'request_other_deadline',
        'request_other_start_date',
        'request_other_end_date',
        'request_other_qty',
        'request_status',
        'subcontract_want_guide_type',
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
        'request_other_status',
        'extend_estimate_id',
        'contact_memo',
        'request_natural_id',
        'status',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
        'operation_finished_flag'
    ];
}
