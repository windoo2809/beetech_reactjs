<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuCustomerUser
 * @property integer    $estimate_id
 * @property integer    $core_id
 * @property integer    $request_id
 * @property integer    $project_id
 * @property integer    $parking_id
 * @property integer    $branch_id
 * @property integer    $estimate_status
 * @property string     $survey_parking_name
 * @property integer    $survey_capacity_qty
 * @property decimal    $survey_site_distance_minute
 * @property decimal    $survey_site_distance_meter
 * @property decimal    $survey_tax_in_flag
 * @property boolean    $survey_total_amt
 */
class CuEstimate extends AppModel
{
    //
    protected $table = 'cu_estimate';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'estimate_id';
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'estimate_id',
        'request_id',
        'project_id',
        'parking_id',
        'branch_id',
        'estimate_status',
        'estimate_expire_date',
        'estimate_cancel_check_flag',
        'estimate_cancel_check_date',
        'survey_parking_name',
        'survey_capacity_qty',
        'survey_site_distance_minute',
        'survey_site_distance_meter',
        'survey_tax_in_flag',
        'survey_total_amt',
        'update_date',
    ];
}
