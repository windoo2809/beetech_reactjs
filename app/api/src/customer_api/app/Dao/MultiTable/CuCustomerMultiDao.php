<?php

namespace App\Dao\MultiTable;

use App\Common\CodeDefinition;
use App\Dao\DaoConstants;
use App\Models\CuCustomer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Dao\CuBaseDao;

class CuCustomerMultiDao extends CuBaseDao
{
    /**
     * Get customer information list
     * @param $customerName
     * @param $customerId
     * @param $page
     * @param $limit
     * @param $role
     * @return mixed
     */
    public function getListCustomerAllOrByCustomerName ( $customerName, $customerId, $nextPage, $pageVolume, $role )
    {
        return $this->getCommonQueryForCustomer($customerName,$customerId, $role)->select(
                'cu_customer.customer_id as customer_id',
                'cu_customer_branch.customer_branch_id as customer_branch_id',
                'cu_customer.customer_name as customer_name',
                'cu_customer_branch.customer_branch_name as customer_branch_name',
                'cu_customer_branch.prefecture as prefecture',
                'cu_customer_branch.city as city',
                'cu_customer_branch.address as address',
            )
            ->offset(($nextPage - CodeDefinition::PAGINATE_DEFAULT_PAGE ) * $pageVolume)
            ->limit($pageVolume)
            ->distinct()
            ->get();
    }

    /**
     * Count the total of customers
     * @param $customerName
     * @param $customerId
     * @param $role
     * @return Builder|Model|object
     */
    public function countTotal ( $customerName, $customerId, $role )
    {
        return $this->getCommonQueryForCustomer($customerName, $customerId, $role)
            ->select(DB::raw('DISTINCT cu_customer_branch.customer_branch_id, cu_customer_branch.customer_branch_id, cu_customer.customer_name, cu_customer_branch.customer_branch_name, cu_customer_branch.prefecture, cu_customer_branch.city, cu_customer_branch.address'))
            ->get()
            ->count();
    }

    /**
     * Get customer details from customer_id and customer_branch_id
     * @param $customerId
     * @param $customerBranchId
     * @return mixed
     */
    public function getInforCustomer($customerId, $customerBranchId)
    {

        $query = CuCustomer::select('cu_customer.customer_id', 'cu_customer_branch.customer_branch_id', 'cu_user_branch.customer_user_id')
            ->join('cu_customer_branch', 'cu_customer_branch.customer_id', '=', 'cu_customer.customer_id')
            ->join('cu_customer_option', 'cu_customer_option.customer_id', '=', 'cu_customer.customer_id')
            ->join('cu_user_branch', 'cu_user_branch.customer_branch_id', '=', 'cu_customer_branch.customer_branch_id')
            ->where([
                ['cu_customer.customer_id', $customerId],
                ['cu_customer_branch.customer_branch_id', $customerBranchId],
                ['cu_customer.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_branch.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_option.status', DaoConstants::STATUS_ACTIVE],
                ['cu_customer_option.plan_type', DaoConstants::CU_CUSTOMER_OPTION_PLAN_TYPE_USE]
            ])
            ->whereDate('cu_customer_option.start_date', '<=',  Carbon::now())
            ->where(function($cuCustomer) {
                $cuCustomer->whereDate('cu_customer_option.end_date', '>=',  Carbon::now())
                    ->orWhereNull('cu_customer_option.end_date');
            });

        return $query->get();
    }

    /**
     * Create a common command to get customer information.
     *
     * @param $customerName, $customerId, $role
     */
    protected function getCommonQueryForCustomer($customerName, $customerId, $role) {
        $query = CuCustomer::join('cu_customer_branch', 'cu_customer.customer_id', '=', 'cu_customer_branch.customer_id')
            ->join('cu_customer_option', 'cu_customer_option.customer_id', '=', 'cu_customer.customer_id', 'left outer')
            ->join('cu_user', 'cu_user.customer_id', '=', 'cu_user.customer_id')
            ->where([
                'cu_customer_option.plan_type' => DaoConstants::CU_CUSTOMER_OPTION_PLAN_TYPE_USE,
                'cu_customer_branch.status'=> DaoConstants::STATUS_ACTIVE,
                'cu_customer.status' => DaoConstants::STATUS_ACTIVE,
                'cu_customer_option.status' => DaoConstants::STATUS_ACTIVE,
                'cu_customer.customer_system_use_flag' => DaoConstants::STATUS_ACTIVE,
                'cu_user.status' => DaoConstants::STATUS_ACTIVE,
            ])
            ->where(function ($subQuery) use ($customerName, $customerId, $role) {
                if (!empty($customerName)){
                    $subQuery->where('cu_customer.customer_name', 'LIKE', "%{$this->escapeLike($customerName)}%");
                }
            })
            ->where('cu_customer.customer_id', $customerId)
            ->whereDate('cu_customer_option.start_date', '<=',  Carbon::now())
            ->where(function($subQuery) {
                 $subQuery->whereDate('cu_customer_option.end_date', '>=',  Carbon::now())
                            ->orWhereNull('cu_customer_option.end_date');
            });

        return $query;
    }
}
