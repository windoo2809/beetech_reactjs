<?php

namespace App\Models;

use App\Models\Model as AppModel;
use Illuminate\Support\Facades\DB;

/**
 * Class CuProject
 * @property integer $project_id
 * @property integer $core_id
 * @property integer $customer_id
 * @property integer $customer_branch_id
 * @property integer $customer_user_id
 * @property integer $branch_id
 * @property string  $construction_number
 * @property string  $site_name
 * @property string  $site_name_kana
 * @property string  $zip_code
 * @property string  $site_prefecture
 * @property string  $address_cd
 * @property string  $city_cd
 * @property string  $site_city
 * @property string  $site_address
 * @property decimal  $latitude
 * @property decimal  $longitude
 * @property datetime  $project_finish_date
 */
class CuProject extends AppModel
{
    //
    protected $table = 'cu_project';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'project_id';
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'project_id',
        'core_id',
        'customer_id',
        'customer_branch_id',
        'customer_user_id',
        'branch_id',
        'construction_number',
        'site_name',
        'site_name_kana',
        'zip_code',
        'site_prefecture',
        'address_cd',
        'city_cd',
        'site_city',
        'site_address',
        'latitude',
        'longitude',
        'project_start_date',
        'project_finish_date',
        'status',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
    ];

    public function scopeApplicationJoinDefault($query) {
        return $query->join('cu_request', 'cu_project.project_id', '=', 'cu_request.project_id')
                ->join('cu_estimate', 'cu_estimate.request_id', '=', 'cu_request.request_id')
                ->join('cu_parking', 'cu_parking.parking_id', '=', 'cu_estimate.parking_id')
                ->join('cu_application', 'cu_application.estimate_id', '=', 'cu_estimate.estimate_id')
                ->join(DB::raw('(SELECT DISTINCT(prefecture_cd), prefecture_name FROM cu_address) as cu_address'), 'cu_project.site_prefecture', '=', 'cu_address.prefecture_cd');
    }
}
