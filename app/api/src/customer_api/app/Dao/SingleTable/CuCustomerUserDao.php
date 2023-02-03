<?php

namespace App\Dao\SingleTable;

use App\Models\CuCustomerUser;
use App\Dao\DaoConstants;
use App\Dao\CuBaseDao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CuCustomerUserDao extends CuBaseDao
{
    /**
     * Get customer contact information from login_id.
     * @param $loginID
     * @return mixed
     */
    public function getInfo ( $loginID )
    {
        return CuCustomerUser::select('customer_user_id', 'customer_id', 'customer_branch_id', 'customer_user_name', 'customer_user_name_kana')
            ->where('customer_user_email', $loginID)->get();
    }


    /**
     * Get the list of customer_user from customer_user_email and customer_branch_id.
     * @param $customerUserEmail
     * @param $customerBranchId
     * @return mixed
     */
    public function getCuCustomerUser($customerUserEmail, $customerBranchId) {
        return CuCustomerUser::where([
            ['customer_user_email', $customerUserEmail],
            ['customer_branch_id', $customerBranchId],
            ['status', DaoConstants::STATUS_ACTIVE]
        ])->first();
    }

    /**
     * get customer user detail without status check
     * @param $customerUserEmail
     * @param $customerBranchId
     * @return mixed
     */
    public function getCustomerUserDetail($customerUserEmail, $customerBranchId) {
        return CuCustomerUser::where([
            ['customer_user_email', $customerUserEmail],
            ['customer_branch_id', $customerBranchId],
        ])->first();
    }



    /**
     * Create a new customer_user.
     * @param $arrData
     * @return array
     */
    public function createCuCustomerUser ( $arrData )
    {
        $arrCustomerUserIds = [];
        if (count($arrData) > 0) {
            foreach ($arrData as $customerUserRecord) {
                $recordCustomerUser = CuCustomerUser::create($customerUserRecord);
                $arrCustomerUserIds[$customerUserRecord['customer_branch_id']] = $recordCustomerUser->customer_user_id;
            }
        }
        return $arrCustomerUserIds;
    }

    /**
     * Get customer_user details from login_id and customer_branch_id.
     * @param $customerBranchId
     * @param $loginId
     * @return mixed
     */
    public function getCustomerUser($customerBranchId, $loginId)
    {
        return CuCustomerUser::select('customer_user_id')
            ->where([
                ['customer_branch_id', $customerBranchId],
                ['customer_user_email', $loginId]
            ])
            ->first();
    }

    /**
     * Create customer_user information from CSV data.
     * @param $arrData
     * @return array
     */
    public function createCuCustomerUserCsv ( $arrData )
    {
        return CuCustomerUser::create($arrData);
    }
}
