<?php

namespace App\Dao;

class DaoConstants
{
    // Define general constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const ORDER_BY_DESC = 'DESC';
    const ORDER_BY_ASC = 'ASC';

    /* Defines the user's lock status. */
    const CU_USER_LOCKED = 1;
    const CU_USER_UNLOCKED = 0;

    /* Defines the first login status. */
    const CU_USER_ACCESS_FLAG = 0;
    const CU_USER_LOGINED = 1;

    /* Define a default password.*/
    const PASSWORD_DEFAULT = 'Landmark@123';

    /* Defines the number of customers managed by the user. */
    const CU_CUSTOMER_NO_MANAGER = 0;
    const CU_CUSTOMER_HAS_ONE_MANAGER = 1;

    /* define system type */
    const CORE_SYSTEM_TYPE = 1;
    const CUSTOMER_SYSTEM_TYPE = 2;

    /** Define the address type. */
    const CU_ADDRESS_COMPANY_FLG_FALSE = 0;
    const CU_ADDRESS_DELETE_FLG_FALSE = 0;

    /** Define plan_type. */
    const CU_CUSTOMER_OPTION_PLAN_TYPE_NOT_USE = 0;
    const CU_CUSTOMER_OPTION_PLAN_TYPE_USE = 1;


    /**
     * Define data_type
     */

    const CU_INFORMATION_DATA_TYPE_ALL_USER = 1;
    const CU_INFORMATION_DATA_TYPE_CUSTOMER = 2;

    /** Define the extension period of Token.*/
    const CU_TOKEN_DEFAULT_TIME_EXTEND = 12;

    /** Define Estimate */
    const STATUS_ESTIMATE_TAKE_ORDER = 2;
    const STATUS_ESTIMATE_CANCEL = 7;
}
