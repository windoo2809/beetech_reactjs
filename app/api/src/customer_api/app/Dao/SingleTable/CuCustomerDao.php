<?php

namespace App\Dao\SingleTable;

use App\Common\CodeDefinition;
use App\Models\CuCustomer;
use App\Dao\DaoConstants;
use App\Dao\CuBaseDao;
use Illuminate\Support\Facades\Auth;

class CuCustomerDao extends CuBaseDao
{
    /**
     * Get customer details by customer_id.
     * @param int $customerId
     *
     * @return collection
     */
    public function getOne($customerId)
    {
        
        $query = CuCustomer::join('cu_user', 'cu_user.customer_id', '=', 'cu_customer.customer_id')
                ->select('cu_customer.customer_id', 'cu_customer.customer_name', 'cu_customer.customer_name_kana', 'cu_customer.construction_number_require_flag')
                        ->where([
                            ['cu_customer.customer_id', $customerId],
                            ['cu_customer.status', DaoConstants::STATUS_ACTIVE],
                            ['cu_user.status', DaoConstants::STATUS_ACTIVE],
                            ['cu_customer.customer_system_use_flag', DaoConstants::STATUS_ACTIVE]
                        ]);

        return $this->authorizationHelper->addDataScopeOfUserInfo($query)
                    ->first();
    }

    /**
     * Get customer details by customer_id.
     * 
     * @param $customerId
     * @return mixed
     */
    public function getCustomerName($customerId)
    {

        return CuCustomer::select('customer_name')
            ->where([
                ['customer_id', $customerId],
                ['status', DaoConstants::STATUS_ACTIVE]
            ])
            ->first();
    }
}
