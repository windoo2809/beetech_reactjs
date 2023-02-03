<?php

namespace App\Dao\MultiTable;

use App\Common\CodeDefinition;
use App\Models\CuCustomer;
use App\Dao\DaoConstants;
use App\Models\CuUser;
use App\Models\CuUserBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Dao\CuBaseDao;

class CuUserMultiDao extends CuBaseDao
{
    /**
     * Get a list of customer branches
     * @param $userId
     * @return mixed
     */
    public function getListCustomerBranch ( $userId )
    {
        /** @var $subQuery */
        $subQuery = CuCustomer::select(
            'cu_customer_branch.customer_branch_id',
            'cu_customer_branch.customer_branch_name',
            'cu_customer_user.customer_user_id',
            'cu_customer.customer_id',
        )
            ->join('cu_customer_option', 'cu_customer_option.customer_id', '=', 'cu_customer.customer_id')
            ->join('cu_customer_branch', 'cu_customer_branch.customer_id', '=', 'cu_customer.customer_id')
            ->join('cu_customer_user', 'cu_customer_user.customer_branch_id', '=', 'cu_customer_branch.customer_branch_id')
            ->where([
                ['cu_customer.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_option.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_branch.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_user.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_option.plan_type', DaoConstants::CU_CUSTOMER_OPTION_PLAN_TYPE_USE]
            ])
            ->whereDate('cu_customer_option.start_date', '<=',  Carbon::now())
            ->where(function($cuSubQuery){
                $cuSubQuery->whereDate('cu_customer_option.end_date', '>=',  Carbon::now())
                    ->orWhereNull('cu_customer_option.end_date');
            });
        $query = CuUserBranch::select(
            'customer.customer_branch_id',
            'customer.customer_branch_name',
            'customer.customer_id'
        )
            ->joinSub($subQuery, 'customer', function ($join) {
                $join->on('cu_user_branch.customer_user_id', '=', 'customer.customer_user_id');
            })
            ->join('cu_user', function ($join) {
                $join->on('cu_user.user_id', '=', 'cu_user_branch.user_id');
                $join->on('cu_user.customer_id', '=', 'customer.customer_id');
            })
            ->where([
                ['cu_user_branch.user_id', $userId],
                ['cu_user_branch.status', DaoConstants::STATUS_ACTIVE]
            ]);
        return $this->authorizationHelper->addDataScopeOfUserInfo($query)->get();
    }

    /**
     * Get user information by email
     * @param $mailAddress
     * @return mixed
     */
    public function getTokenByMaillAddress( $mailAddress )
    {
        return CuUser::join('cu_token', 'cu_user.user_id', '=', 'cu_token.user_id')
            ->where([
                ['cu_user.login_id', $mailAddress],
                ['cu_user.status', DaoConstants::STATUS_ACTIVE]
            ])
            ->whereDate('expire', '>=', Carbon::now())
            ->select('cu_user.user_id as cu_user_id', 'cu_token.token as cu_token', 'cu_token.expire as cu_token_expire')
            ->latest('token_id')->first();
    }

    /**
     * Get user information for authentication
     */

    public function getUserForAuthentice ($loginId)
    {

        return CuUser::join('cu_user_branch', function ($join) {
                $join->on('cu_user.user_id', '=', 'cu_user_branch.user_id');
                $join->on('cu_user.customer_id', '=', 'cu_user_branch.customer_id');
            })
            ->select('cu_user.user_id', 'cu_user.customer_id', 'cu_user_branch.customer_branch_id', 'cu_user.login_id', 'cu_user.customer_user_name', 'cu_user.customer_user_name_kana', 'cu_user.role', 'cu_user.access_flg')
            ->where('cu_user.login_id', $loginId)
            ->where('cu_user_branch.belong', '!=', CodeDefinition::BELONG_NO_AFFILIATION)
            ->where('cu_user.status', DaoConstants::STATUS_ACTIVE)
            ->where('cu_user.user_lock', '=', DaoConstants::CU_USER_UNLOCKED)
            ->where('cu_user.update_system_type', DaoConstants::CUSTOMER_SYSTEM_TYPE)
            ->where('cu_user.role', '!=', CodeDefinition::ROLE_NO_PERMISSION)
            ->first();
    }

    /**
     * Get user information list
     * @param null $branchName
     * @param null $userName
     * @param null $roleID
     * @param $customerID
     * @param null $page
     * @param null $limit
     * @param $sort
     * @return mixed
     */
    public function getListUser ( $branchName, $userName, $roleID, $customerID, $nextPage, $pageVolume, $sort )
    {

        $cuUser = $this->commonQueryForListUser($branchName, $userName, $roleID, $customerID)
            ->select('cu_user.user_id as user_id',
                'cu_customer_branch.customer_branch_name as customer_branch_name',
                'cu_user.customer_user_name as customer_user_name',
                DB::raw('CASE WHEN cu_user.user_lock = TRUE THEN "' . trans("attributes.cu_user.locked") . '" ELSE "-" END as system_use_permission'),
                'cu_role.role_name as cu_role_name',
                'cu_user.login_id as login_id'
            );
            if (!empty($sort) && count($sort) > 0){
                $cuUser->orderBy($sort['column'], $sort['order']);
            }
            $cuUser->orderBy('cu_user.user_id', 'ASC')
                    ->offset(($nextPage -  CodeDefinition::PAGINATE_DEFAULT_PAGE ) * $pageVolume)
                    ->limit($pageVolume);
            return $cuUser->get();
    }

    /**
     * Get user details
     * @param $userID
     * @param $customerID
     * @return Builder|Model|object|null
     */
    public function getDetailUser ( $userID, $customerID )
    {



        $query = CuUser::join('cu_user_branch', 'cu_user_branch.user_id', '=', 'cu_user.user_id')
            ->join('cu_role', 'cu_role.role', '=', 'cu_user.role')
            ->join('cu_customer_branch', 'cu_customer_branch.customer_branch_id', '=', 'cu_user_branch.customer_branch_id')
            ->where([
                    ['cu_user.status', DaoConstants::STATUS_ACTIVE],
                    ['cu_customer_branch.status', DaoConstants::STATUS_ACTIVE],
                    ['cu_user_branch.status', DaoConstants::STATUS_ACTIVE],
                    ['cu_user.user_id', $userID],
                    ['cu_user.customer_id', $customerID],
                    ['cu_user.role', '!=', CodeDefinition::ROLE_SUPER_USER]
                ])
            ->select('cu_user.user_id as user_id',
                DB::raw('GROUP_CONCAT(cu_customer_branch.customer_branch_name) AS customer_branch_name'),
                'cu_user.customer_user_name as customer_user_name',
                'cu_user.customer_user_name_kana as customer_user_name_kana',
                'cu_user.customer_reminder_sms_flag as customer_reminder_sms_flag',
                'cu_user.customer_user_tel as customer_user_tel',
                'cu_user.login_id as login_id',
                'cu_role.role_name as role_name',
                DB::raw('CASE WHEN cu_user_branch.belong = '. CodeDefinition::BELONG_BELONGING .' THEN "' . trans("attributes.cu_user.belong_status") . '" WHEN cu_user_branch.belong = '. CodeDefinition::BELONG_NO_AFFILIATION .' THEN"' . trans("attributes.cu_user.no_belong")  . '" ELSE NULL END as belong_name'),
                DB::raw('GROUP_CONCAT(cu_customer_branch.customer_branch_id) AS customer_branch_id'),
                DB::raw('CASE WHEN cu_user.user_lock = TRUE THEN "' . trans("attributes.cu_user.locked") . '" ELSE "' . trans("attributes.cu_user.un_lock")  . '" END as lock_status_name'),
                'cu_user.role as role',
                'cu_user.user_lock as user_lock',
                'cu_user_branch.belong as belong',
            );
        return $this->authorizationHelper->addDataScopeOfUserInfo($query)->first();
    }

    /**
     * Count the total of users
     * @param $branchName
     * @param $userName
     * @param $roleID
     * @param $customerID
     * @return Builder[]|Collection
     */
    public function getTotalUser ( $branchName, $userName, $roleID, $customerID)
    {


        return $this->commonQueryForListUser($branchName, $userName, $roleID, $customerID)
                    ->select(DB::raw('COUNT(cu_user.user_id) as cnt'))
                    ->get();
    }

    /** Build a common query to get user information */
    protected function commonQueryForListUser ($branchName, $userName, $roleID, $customerID) {
        $query = CuUser::join('cu_user_branch', 'cu_user_branch.user_id', '=', 'cu_user.user_id')
            ->join('cu_role', 'cu_role.role', '=', 'cu_user.role')
            ->join('cu_customer_branch', 'cu_customer_branch.customer_branch_id', '=', 'cu_user_branch.customer_branch_id')
            ->where(function ($cuUserTotal) use ($branchName, $userName, $customerID, $roleID) {
                if (!empty($branchName)){
                    $cuUserTotal->where('cu_customer_branch.customer_branch_name', 'LIKE', "%{$this->escapeLike($branchName)}%");
                }
                if (!empty($userName)){
                    $cuUserTotal->where('cu_user.customer_user_name', 'LIKE', "%{$this->escapeLike($userName)}%");
                }
                if (!empty($roleID)){
                    $cuUserTotal->where('cu_user.role', $roleID);
                } else {
                    $cuUserTotal->where('cu_user.role', '!=', CodeDefinition::ROLE_SUPER_USER);
                }
                $cuUserTotal->where([
                    ['cu_user.status', DaoConstants::STATUS_ACTIVE],
                    ['cu_user.customer_id', $customerID],
                ]);
            });

        return $this->authorizationHelper->addDataScopeOfUserInfo($query);
    }

    /**
     * Get customer branch information for authentication
     * @param $loginId
     * @return mixed
     */
    public function getInfoUserBranchForCheckUser ( $loginId )
    {
        $query = $this->getUserInfoCommonQuery($loginId);
        return $query->get();
    }

    /**
     * Get customer branch information for authentication
     * @param $loginId
     * @return mixed
     */
    public function getInfoUserBranchForAuthenticate ( $loginId )
    {
        $query = $this->getUserInfoCommonQuery($loginId);
        return $this->authorizationHelper->addDataScopeOfUserInfo($query)->get();
    }

    /**
     * Get customer branch information for first login
     * @param $loginId
     * @return mixed
     */
    public function getInfoUserBranchForFirstLogin ( $loginId )
    {
        return $this->getUserInfoCommonQuery($loginId)->get();
    }

    /**
     * Create a common query for get user information.
     *
     * @param $loginId
     */

    protected function getUserInfoCommonQuery ($loginId) {
         /** @var $subQuery */
         $subQuery = CuCustomer::select(
            'cu_customer.customer_id',
            'cu_customer_branch.customer_branch_id',
            'cu_customer_user.customer_user_id'
        )
        ->join('cu_customer_option', 'cu_customer_option.customer_id', '=', 'cu_customer.customer_id')
        ->join('cu_customer_branch', 'cu_customer_branch.customer_id', '=', 'cu_customer.customer_id')
        ->join('cu_customer_user', 'cu_customer_user.customer_branch_id', '=', 'cu_customer_branch.customer_branch_id')
        ->where([
            ['cu_customer.status', DaoConstants::STATUS_ACTIVE],
            ['cu_customer_option.status', DaoConstants::STATUS_ACTIVE],
            ['cu_customer_branch.status', DaoConstants::STATUS_ACTIVE],
            ['cu_customer_user.status', DaoConstants::STATUS_ACTIVE],
            ['cu_customer_option.plan_type', DaoConstants::CU_CUSTOMER_OPTION_PLAN_TYPE_USE]
        ])
        ->whereDate('cu_customer_option.start_date', '<=',  Carbon::now())
        ->where(function($cuSubQuery){
            $cuSubQuery->whereDate('cu_customer_option.end_date', '>=',  Carbon::now())
                ->orWhereNull('cu_customer_option.end_date');
        });

        $query = CuUser::select(
            'customer.customer_id',
            'customer.customer_branch_id',
            'customer.customer_user_id'
        )
        ->join('cu_user_branch', 'cu_user_branch.user_id', '=', 'cu_user.user_id')
        ->joinSub($subQuery, 'customer', function ($join) {
            $join->on('cu_user_branch.customer_user_id', '=', 'customer.customer_user_id');
        })->where([
            ['cu_user.login_id', $loginId],
            ['cu_user.status', DaoConstants::STATUS_ACTIVE],
            ['cu_user_branch.status', DaoConstants::STATUS_ACTIVE]
        ]);

        return $query;
    }

    /**
     * Get cu_user_branch when delete branch in import csv
     * @param $loginID
     * @param $customerBranchIds
     * @return mixed
     */
    public function getUserBranch( $loginID , $customerBranchIds ) {
        return CuUser::select(DB::raw('COUNT(cu_user_branch.customer_branch_id) as cnt'))
            ->join('cu_user_branch', 'cu_user_branch.user_id', '=', 'cu_user.user_id')
            ->where([
                ['cu_user.login_id', $loginID],
                ['cu_user.status', DaoConstants::STATUS_ACTIVE],
                ['cu_user_branch.status', DaoConstants::STATUS_ACTIVE]
            ])
            ->whereNotIn('cu_user_branch.customer_branch_id', $customerBranchIds )
            ->get();
    }
}
