<?php

namespace App\Dao\MultiTable;

use App\Common\CodeDefinition;
use App\Models\CuProject;
use App\Dao\DaoConstants;
use App\Dao\CuBaseDao;
use Illuminate\Support\Facades\DB;
use App\Models\VProjectList;

class CuProjectMultiDao extends CuBaseDao
{
    /**
     * Get a list of construction information.
     */
    public function getListProject($requestData, $user)
    {

        $query = $this->buildConditionQuery($requestData, $user);

        $query->select('v_project_list.project_id',
                'v_project_list.construction_number',
                'v_project_list.site_name',
                'v_project_list.site_name_kana',
                'v_project_list.site_latitude',
                'v_project_list.site_longitude',
                'v_project_list.site_prefecture',
                'v_project_list.prefecture_name',
                'v_project_list.site_city',
                'v_project_list.site_address',
                'v_project_list.project_start_date',
                'v_project_list.project_finish_date',
                'v_project_list.customer_id',
                'v_project_list.customer_branch_id',
                'v_project_list.customer_branch_name',
                'v_project_list.customer_user_id',
                'v_project_list.customer_user_name',
                'request_id',
                'v_project_list.request_natural_id',
                'request_type',
                'estimate_deadline',
                'request_status',
                'v_project_list.request_other_status',
                'v_project_list.car_qty',
                'v_project_list.light_truck_qty',
                'v_project_list.truck_qty',
                'v_project_list.other_car_qty',
                'v_project_list.request_other_qty',
                'v_project_list.is_exsits_route_map_file',
                'estimate_id',
                'v_project_list.estimate_natural_id',
                'estimate_status',
                'estimate_expire_date',
                'v_project_list.is_exsits_survey_estimate_file',
                'v_project_list.is_exsits_estimate_file',
                'contract_id',
                'contract_status',
                'quote_available_start_date',
                'quote_available_end_date',
                'extension_type',
                'v_project_list.cannot_extention_flag',
                'v_project_list.payment_status',
                'v_project_list.is_exsits_order_file',
                'v_project_list.is_exsits_contract_file',
                'v_project_list.invoice_id',
                'parking_id',
                'parking_name',
                'v_project_list.is_exsits_invoice_file',
                'progress_status',
                'progress_date',
                'application_id',
                'application_user_id',
                'v_project_list.application_comment',
                'approval_user_id',
                'v_project_list.contact_memo',
                'v_project_list.approval_comment',
                'v_project_list.approval_user_name',
                'v_project_list.application_status',
                'v_project_list.application_user_name',
                'v_project_list.application_date',
                'v_project_list.approval_date',
                'v_project_list.quote_capacity_qty',
                'v_project_list.zip_code',
                'v_project_list.update_date',
                'cc_email',
                'want_end_date',
            );

        return $query->orderBy('v_project_list.update_date', 'desc')->get();
    }

    /**
     * Count the total construction information.
     */
    public function countProject($requestData, $user) {
        $query =  $this->buildConditionQuery($requestData, $user);
        return $query->select(DB::raw('count(v_project_list.project_id) as total_count'))->first();
    }

    public function getDetailCount($requestData, $user) {
        $query =  $this->buildConditionQuery($requestData, $user);
        return $query->select(DB::raw('count(v_project_list.project_id) as project_count,
                                    count(distinct v_project_list.request_type) as request_count,
                                    count(DISTINCT v_project_list.parking_id) as parking_count'))
                    ->first();
    }

    /**
     * build common query
     */
    protected function buildConditionQuery($requestData, $user) {
        $query = VProjectList::join('cu_project', 'cu_project.project_id', '=', 'v_project_list.project_id')
                                ->where('v_project_list.customer_id', $user->customer_id);

        $query = $this->authorizationHelper->addDataScopeOfProject($query);

        if (isset($requestData['search_word'])) {
            $keySearch = $this->convertSpaceToPercent($this->escapeLike($requestData['search_word']));
            $query->where('search_keyword', 'like', '%' . $keySearch . '%');
        }

        if (isset($requestData['progress_status'])) {
            $query->where('progress_status', '=', $requestData['progress_status']);
        }

        if (isset($requestData['search_conditions'])) {
            if ($requestData['search_conditions'] == CodeDefinition::SEARCH_CONDITION_ONE) {
                $query->whereRaw('v_project_list.estimate_expire_date <= CURRENT_TIMESTAMP + INTERVAL 1 DAY');
                $query->where('v_project_list.estimate_status', CodeDefinition::ESTIMATE_STATUS_WAITING_ORDER_CREATED_SURVEY);
            }

            if ($requestData['search_conditions'] == CodeDefinition::SEARCH_CONDITION_SECOND) {
                $query->where('v_project_list.contract_status', CodeDefinition::CONTRACT_STATUS_WAITING_FOR_CONTRACT_TO_BE_RRETURNED);
            }

            if ($requestData['search_conditions'] == CodeDefinition::SEARCH_CONDITION_THIRD) {
                $query->whereRaw('v_project_list.quote_available_end_date BETWEEN CURRENT_TIMESTAMP + INTERVAL '. CodeDefinition::SEARCH_START_DAY .' DAY AND CURRENT_TIMESTAMP + INTERVAL '. CodeDefinition::SEARCH_END_DAY .' DAY');
                $query->where('v_project_list.extension_type', CodeDefinition::EXTENSION_TYPE_UNCONFIRMED);
            }
        }

        return $query;
    }

    /**
     * Get construction information by project_id.
     */
    public function getProjectById($projectId, $user)
    {
        $query = CuProject::join('cu_customer_branch', 'cu_project.customer_branch_id', '=', 'cu_customer_branch.customer_branch_id')
            ->join('cu_customer_user', 'cu_project.customer_user_id', '=', 'cu_customer_user.customer_user_id')
            ->join(DB::raw('(SELECT DISTINCT(prefecture_cd), prefecture_name
                                    FROM cu_address
                                    WHERE cu_address.company_flg = '. DaoConstants::CU_ADDRESS_COMPANY_FLG_FALSE .'
                                        AND cu_address.delete_flg = '. DaoConstants::CU_ADDRESS_DELETE_FLG_FALSE .'
                                  ) as cu_address'
                ),
                'cu_project.site_prefecture', '=', 'cu_address.prefecture_cd')
            ->select(
                'cu_project.project_id',
                'cu_project.customer_id',
                'cu_project.customer_branch_id',
                'cu_customer_branch.customer_branch_name',
                'cu_project.customer_user_id',
                'cu_customer_user.customer_user_name',
                'cu_project.branch_id',
                'cu_project.construction_number',
                'cu_project.site_name',
                'cu_project.site_name_kana',
                'cu_project.zip_code',
                'cu_project.site_prefecture',
                'cu_address.prefecture_name',
                'cu_project.address_cd',
                'cu_project.city_cd',
                'cu_project.site_city',
                'cu_project.site_address',
                'cu_project.latitude',
                'cu_project.longitude',
                'cu_project.project_start_date',
                'cu_project.project_finish_date'
            )->where([
                'cu_project.project_id' => $projectId,
                'cu_project.status' => DaoConstants::STATUS_ACTIVE,
                'cu_customer_branch.customer_id' => $user->customer_id,
                'cu_customer_branch.status' => DaoConstants::STATUS_ACTIVE,
                'cu_customer_user.customer_id' => $user->customer_id,
                'cu_customer_user.status' => DaoConstants::STATUS_ACTIVE
            ]);

        $query = $this->authorizationHelper->addDataScopeOfProject($query);

        return $query->first();
    }


    /**
     * List Project Query
     */
    public function listProjectQuery($request, $user)
    {
        $response = [];

        $query = VProjectList::where('v_project_list.customer_id', '=', $user->customer_id)
        ->select(
            'v_project_list.project_id',
            'v_project_list.construction_number',
            'v_project_list.site_name',
            'v_project_list.site_name_kana',
            'v_project_list.site_prefecture',
            'v_project_list.prefecture_name',
            'v_project_list.site_city',
            'v_project_list.site_address',
            'v_project_list.project_start_date',
            'v_project_list.project_finish_date',
            'v_project_list.customer_id',
            'v_project_list.customer_branch_id',
            'v_project_list.customer_branch_name',
            'v_project_list.customer_user_id',
            'v_project_list.customer_user_name',
            DB::raw('count(v_project_list.contact_memo) as contact_memo_count')
        );

        if ( isset( $request['progress_status'] ) ) {
            $progress_status = (array) $request['progress_status'];
            $query->whereIn('v_project_list.progress_status', $progress_status);
        }

        if ( isset( $request['project_start_date_from'] ) ) {
            $query->whereDate('v_project_list.project_start_date', '>=', $request['project_start_date_from']);
        }

        if ( isset( $request['project_start_date_to'] ) ) {
            $query->whereDate('v_project_list.project_start_date', '<=', $request['project_start_date_to']);
        }

        if ( isset( $request['project_finish_date_from'] ) ) {
            $query->whereDate('v_project_list.project_finish_date', '>=', $request['project_finish_date_from']);
        }

        if ( isset( $request['project_finish_date_to'] ) ) {
            $query->whereDate('v_project_list.project_finish_date', '<=', $request['project_finish_date_to']);
        }

        if ( isset( $request['quote_available_start_date_from'] ) ) {
            $query->whereDate('v_project_list.quote_available_start_date', '>=', $request['quote_available_start_date_from']);
        }

        if ( isset( $request['quote_available_start_date_to'] ) ) {
            $query->whereDate('v_project_list.quote_available_start_date', '<=', $request['quote_available_start_date_to']);
        }

        if ( isset( $request['quote_available_end_date_from'] ) ) {
            $query->whereDate('v_project_list.quote_available_end_date', '>=', $request['quote_available_end_date_from']);
        }

        if ( isset( $request['quote_available_end_date_to'] ) ) {
            $query->whereDate('v_project_list.quote_available_end_date', '<=', $request['quote_available_end_date_to']);
        }

        if ( isset( $request['estimate_status'] ) ) {
            $estimate_status = (array) $request['estimate_status'];
            $query->whereIn('v_project_list.estimate_status', $estimate_status);
        }

        if ( isset( $request['payment_status'] ) ) {
            $payment_status = (array) $request['payment_status'];

            $query->whereIn('v_project_list.payment_status', $payment_status);
        }

        if ( isset( $request['search_keyword'] ) ) {
            $keyword = $this->convertSpaceToPercent( $this->escapeLike( $request['search_keyword'] ) );

            $query->where('v_project_list.search_keyword', 'like', '%' . $keyword . '%');
        }

        if ( isset( $request['search_conditions'] ) ) {
            if ( $request['search_conditions'] == CodeDefinition::SEARCH_CONDITION_ONE ) {
                $query->whereRaw('v_project_list.estimate_expire_date <= CURRENT_TIMESTAMP + INTERVAL 1 DAY');
                $query->where('v_project_list.estimate_status', CodeDefinition::ESTIMATE_STATUS_WAITING_ORDER_CREATED_SURVEY);
            }

            if ($request['search_conditions'] == CodeDefinition::SEARCH_CONDITION_SECOND) {
                $query->where('v_project_list.contract_status', CodeDefinition::CONTRACT_STATUS_WAITING_FOR_CONTRACT_TO_BE_RRETURNED);
            }

            if ($request['search_conditions'] == CodeDefinition::SEARCH_CONDITION_THIRD) {
                $query->whereRaw('v_project_list.quote_available_end_date BETWEEN CURRENT_TIMESTAMP + INTERVAL '. CodeDefinition::SEARCH_START_DAY .' DAY AND CURRENT_TIMESTAMP + INTERVAL '. CodeDefinition::SEARCH_END_DAY .' DAY');
                $query->where('v_project_list.extension_type', CodeDefinition::EXTENSION_TYPE_UNCONFIRMED);
            }
        }

        if ( isset( $request['application_status'] ) ) {
            $application_status = (array) $request['application_status'];
            $query->whereIn('v_project_list.application_status', $application_status);
        }

        $query = $this->authorizationHelper->checkDataScopeOfProject($query);
        $query = $query->groupBy(
            'v_project_list.project_id',
            'v_project_list.construction_number',
            'v_project_list.site_name',
            'v_project_list.site_name_kana',
            'v_project_list.site_prefecture',
            'v_project_list.prefecture_name',
            'v_project_list.site_city',
            'v_project_list.site_address',
            'v_project_list.project_start_date',
            'v_project_list.project_finish_date',
            'v_project_list.customer_id',
            'v_project_list.customer_branch_id',
            'v_project_list.customer_branch_name',
            'v_project_list.customer_user_id',
            'v_project_list.customer_user_name'
        );

        // total data
        $response['total'] = $query->get()->count();

        // data pagination
        $query->limit($request['limit']);
        $query->offset($request['offset']);

        if ( isset($request['order']) && $request['order'] == 1 ) {
            $query->orderBy('v_project_list.project_start_date', 'desc');
        } else {
            $query->orderBy('v_project_list.project_start_date', 'asc');
        }

        $response['data'] = $query->get();

        return $response;
    }

    /**
     * get Project list By Estimate ID
     */
    public function getProjecListByEstimateID($estimateId, $user)
    {
        $response = [];
        $query = VProjectList::where('v_project_list.customer_id', '=', $user->customer_id)
        ->where('v_project_list.estimate_id','=', $estimateId)
        ->select(
            'v_project_list.project_id',
            'v_project_list.request_id',
            'v_project_list.estimate_id',
            'v_project_list.contract_id',
            'v_project_list.quote_available_start_date',
            'v_project_list.quote_available_end_date',
            'v_project_list.extension_type',
            'v_project_list.invoice_id',
            'v_project_list.application_id',
            'v_project_list.approval_user_id',
            'v_project_list.application_status'
        );

        $query = $this->authorizationHelper->checkDataScopeOfProject($query);

        $response = $query->orderBy('v_project_list.quote_available_start_date', 'desc')->get();

        return $response;
    }
}
