<?php

namespace App\Dao\SingleTable;

use App\Common\CodeDefinition;
use App\Models\CuRequest;
use App\Dao\DaoConstants;
use App\Models\VProjectList;
use Illuminate\Support\Arr;
use App\Dao\CuBaseDao;
use Illuminate\Support\Facades\DB;

class CuRequestDao extends CuBaseDao
{
    /**
     * Request information is registered when new construction information is created.
     *
     * @param array $estimateData, $estimateData, $projectId.
     */

    public function createForNewProject($estimateData, $projectId, $userId)
    {

        $paramInsert = $this->setDataForInsert($estimateData, $projectId, $userId);
        $id = CuRequest::create($paramInsert)->request_id;

        return $id;
    }

   
    /**
     * Data set at registration.
     *
     * @param $estimateData, $projectId, $userId.
     */
    protected function setDataForInsert($estimateData, $projectId, $userId) {
        $paramInsert = [];

        $paramInsert['project_id']                      = $projectId;
        $request_date = date('Y-m-d H:i:s');
        if(isset($estimateData['request_date']) && $estimateData['request_date']){
            $request_date = $estimateData['request_date'];
        }
        $paramInsert['request_date']                    = $request_date;
        $paramInsert['estimate_deadline']               = Arr::get($estimateData, 'estimate_deadline');
        $paramInsert['request_type']                    = Arr::get($estimateData, 'request_type');
        $paramInsert['want_start_date']                 = Arr::get($estimateData, 'want_start_date');
        $paramInsert['want_end_date']                   = Arr::get($estimateData, 'want_end_date');
        $paramInsert['car_qty']                         = Arr::get($estimateData, 'car_qty');
        $paramInsert['light_truck_qty']                 = Arr::get($estimateData, 'light_truck_qty');
        $paramInsert['truck_qty']                       = Arr::get($estimateData, 'truck_qty');
        $paramInsert['other_car_qty']                   = Arr::get($estimateData, 'other_car_qty');
        $paramInsert['other_car_detail']                = Arr::get($estimateData, 'other_car_detail');
        $paramInsert['send_destination_type']           = Arr::get($estimateData, 'send_destination_type');
        $paramInsert['want_guide_type']                 = Arr::get($estimateData, 'want_guide_type');
        $paramInsert['cc_email']                        = Arr::get($estimateData, 'cc_email');
        $paramInsert['customer_other_request']          = Arr::get($estimateData, 'customer_other_request');
        $paramInsert['request_other_deadline']          = Arr::get($estimateData, 'request_other_deadline');
        $paramInsert['request_other_start_date']        = Arr::get($estimateData, 'request_other_start_date');
        $paramInsert['request_other_end_date']          = Arr::get($estimateData, 'request_other_end_date');
        $paramInsert['request_other_qty']               = Arr::get($estimateData, 'request_other_qty');
        $paramInsert['request_status']                  = CodeDefinition::REQUEST_STATUS_RECEPTION;
        $paramInsert['subcontract_want_guide_type']     = Arr::get($estimateData, 'subcontract_want_guide_type');
        $paramInsert['subcontract_name']                = Arr::get($estimateData, 'subcontract_name');
        $paramInsert['subcontract_kana']                = Arr::get($estimateData, 'subcontract_kana');
        $paramInsert['subcontract_branch_name']         = Arr::get($estimateData, 'subcontract_branch_name');
        $paramInsert['subcontract_branch_kana']         = Arr::get($estimateData, 'subcontract_branch_kana');
        $paramInsert['subcontract_branch_tel']          = Arr::get($estimateData, 'subcontract_branch_tel');
        $paramInsert['subcontract_user_division_name']  = Arr::get($estimateData, 'subcontract_user_division_name');
        $paramInsert['subcontract_user_name']           = Arr::get($estimateData, 'subcontract_user_name');
        $paramInsert['subcontract_user_kana']           = Arr::get($estimateData, 'subcontract_user_kana');
        $paramInsert['subcontract_user_email']          = Arr::get($estimateData, 'subcontract_user_email');
        $paramInsert['subcontract_user_tel']            = Arr::get($estimateData, 'subcontract_user_tel');
        $paramInsert['subcontract_user_fax']            = Arr::get($estimateData, 'subcontract_user_fax');
        $paramInsert['subcontract_reminder_sms_flag']   = Arr::get($estimateData, 'subcontract_reminder_sms_flag');
        $paramInsert['create_user_id']                  = $userId;
        $paramInsert['create_system_type']              = DaoConstants::CUSTOMER_SYSTEM_TYPE;
        $paramInsert['status']                          = DaoConstants::STATUS_ACTIVE;

        return $paramInsert;
    }

}
