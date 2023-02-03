<?php

namespace App\Services;

use App\Common\CodeDefinition;
use App\Dao\DaoConstants;
use App\Dao\MultiTable\CuUserMultiDao;
use App\Dao\MultiTable\CuCustomerMultiDao;
use App\Dao\SingleTable\CuCustomerDao;
use App\Dao\SingleTable\CuCustomerOptionDao;
use App\Dao\SingleTable\CuTokenDao;
use App\Dao\SingleTable\CuUserBranchDao;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Services\Service as BaseService;
use App\Dao\SingleTable\CuUserDao;
use App\Dao\SingleTable\CuCustomerUserDao;
use App\Common\CustomResponse;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth as FireBaseAuth;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class AuthService extends BaseService
{
    const SYSTEM_USAGE_VALIDATE_LOGIN = 1;
    const SYSTEM_USAGE_VALIDATE_ACCESS_TOKEN = 2;
    /**
     * custom response data
     */
    protected CustomResponse $customResponse;
    /**
     * firebaseAuth
     */
    protected FireBaseAuth $fireBaseAuth;
    /**
     * @var CuUserMultiDao
     */
    protected CuUserMultiDao $cuUserMultiDao;
    /**
     * @var CuCustomerMultiDao
     */
    protected CuCustomerMultiDao $cuCustomerMultiDao;
    /**
     * @var CuUserDao
     */
    protected CuUserDao $cuUserDao;
    /**
     * @var CuCustomerOptionDao
     */
    protected CuCustomerOptionDao $cuCustomerOptionDao;

    /**
     * CuSubContractService constructor.
     *
     * @param CustomResponse $customResponse
     * @param FireBaseAuth $fireBaseAuth
     * @param CuUserMultiDao $cuUserMultiDao
     * @param CuCustomerMultiDao $cuCustomerMultiDao
     * @param CuUserDao $cuUserDao
     * @param CuCustomerOptionDao $cuCustomerOptionDao
     */
    public function __construct(
            CustomResponse $customResponse,
            FireBaseAuth $fireBaseAuth,
            CuUserMultiDao $cuUserMultiDao,
            CuCustomerMultiDao $cuCustomerMultiDao,
            CuUserDao $cuUserDao,
            CuCustomerOptionDao $cuCustomerOptionDao,
        )
    {
        $this->customResponse = $customResponse;
        $this->fireBaseAuth = $fireBaseAuth;
        $this->cuUserMultiDao = $cuUserMultiDao;
        $this->cuCustomerMultiDao = $cuCustomerMultiDao;
        $this->cuUserDao = $cuUserDao;
        $this->cuCustomerOptionDao = $cuCustomerOptionDao;
    }

    /**
     * Check authenticate and get information
     * @return array|false
     */
    public function checkAuth($flagGenerateAccessToken = null)
    {
        $signInResult = null;
        /** Authenticate user information to Google identity platform. */
        $credentials = request(['login_id', 'password']);
        $loginId = $credentials['login_id'];
        $credentials['status'] = DaoConstants::STATUS_ACTIVE;
        try {
            $signInResult = $this->fireBaseAuth->signInWithEmailAndPassword($loginId, $credentials['password']);
        } catch (\Exception $e) {
            Log::error('The Google identity platform has been authenticated that the user information is incorrect (authenticated by email '. $this->removeEmailDomain($loginId) .').');
            return false;
        }

        /** Check the system usage status. */
        $customerLoginId = request('customer_login_id');
        $customerSystem = $this->validateSystemUsageStatus($customerLoginId, self::SYSTEM_USAGE_VALIDATE_LOGIN);

        if (!$customerSystem) {
            Log::error('system use check: The customer_login_id('. $customerLoginId .') is not exists');
            return false;
        }
        $customerId = $customerSystem->customer_id;
        /** Check user information by login_id */
        $user = $this->getUserForAuthentice($loginId, $customerId);
        if (!$user){
            Log::error('User information (email: '. $this->removeEmailDomain($loginId) .' and customer_id:'. $customerId .') is not found');
            return false;
        }

        $isActive = $user->status;
        $isLocked = $user->user_lock;
        $isFirstLogin = $user->access_flg;
        $userRole = $user->role;
        $user = $user->only(['user_id', 'customer_id', 'role', 'customer_user_name', 'customer_user_name_kana']);

        if ($isActive === false) {
            Log::error('User with user_id '. $user['user_id'] .' is inactive. $user->status='. $isActive);
            return false;
        }

        if ($userRole === CodeDefinition::ROLE_NO_PERMISSION) {
            Log::error('User with user_id '. $user['user_id'] .' does not have permission to login. $user->role=' .$userRole);
            return false;
        }

        if ($isLocked === DaoConstants::CU_USER_LOCKED) {
            Log::error('User with user_id '. $user['user_id'] .' is locked. $user->user_lock='. $isLocked);
            return false;
        }

        if ($isFirstLogin === DaoConstants::CU_USER_ACCESS_FLAG) {
            Log::error('User with user_id '. $user['user_id'] .' is logged in for the first time. $user->access_flag='. $isFirstLogin);
            $user['customer_id'] = null;
            $user['customer_branch_id'] = null;
            $user['customer_user_id'] = null;
            $user['customer_login_id'] = null;
            // Create return information.
            $response = $this->makeResponseData($signInResult, $user, CodeDefinition::REDIRECT_TO_CUSTOMER_LAYOUT_FIRST_ACCESS);
            if (!$flagGenerateAccessToken) {
                /** Generate a token for the first login */
                $newToken = Hash('sha256', ($user['user_id'] . Carbon::now()));
                $flagGenerateTokenLoginFirstTime = $this->generateTokenLoginFirstTime($credentials['login_id'], $user['user_id'], $newToken);
                if ($flagGenerateTokenLoginFirstTime) {
                    $response['token'] = $newToken;
                }
                Log::debug('Succeeded in generating a one-time token for the first login by user with user_id '. $user['user_id']);
            }
            return $response;
        }
        /** Check is super user ?. */
        if ($user['role'] === CodeDefinition::ROLE_SUPER_USER) {
            Log::debug('User with user_id '. $user['user_id'] .' is a supper user. $user->role='. $user['role']);
            $user['customer_id'] = null;
            $user['customer_branch_id'] = null;
            $user['customer_user_id'] = null;
            /** Add new param customer_login_id in access token */
            $user['customer_login_id'] = $customerSystem['customer_id'];
            // Create return information.
            return $this->makeResponseData($signInResult, $user, CodeDefinition::REDIRECT_TO_ADMIN_LAYOUT);
        }

        /** Log in user by ID*/
        Auth::loginUsingId($user['user_id']);

        /** Get customer details */
        $customerData = $this->cuUserMultiDao->getInfoUserBranchForAuthenticate($credentials['login_id']);
        if (empty($customerData) || count($customerData) == 0) {
            Log::error('User with user_id '. $user['user_id'] .' is no branch that matches the user information. $customerData is empty');
            return false;
        }

        /** Check if the user information is correct */
        $countCustomerUser = count($customerData);
        if ($countCustomerUser === DaoConstants::CU_CUSTOMER_NO_MANAGER) {
            Log::error('User with user_id '. $user['user_id'] .' is no branch that matches the user information. $countCustomerUser='. $countCustomerUser);
            return false;

        } else if ($countCustomerUser === DaoConstants::CU_CUSTOMER_HAS_ONE_MANAGER) {
            Log::debug('User with user_id '. $user['user_id'] .' has only one branch. $countCustomerUser='. $countCustomerUser);
            $user['customer_id'] = $customerData[0]['customer_id'];
            $user['customer_branch_id'] = $customerData[0]['customer_branch_id'];
            $user['customer_user_id'] = $customerData[0]['customer_user_id'];
            $user['customer_login_id'] = $customerSystem['customer_id'];
            // Create return information.
            $response = $this->makeResponseData($signInResult, $user, CodeDefinition::REDIRECT_TO_CUSTOMER_LAYOUT_DEFAULT);

        } else {
            Log::debug('User with user_id '. $user['user_id'] .' has many branches. $countCustomerUser='. $countCustomerUser);
            $user['customer_id'] = null;
            $user['customer_branch_id'] = null;
            $user['customer_user_id'] = null;
            $user['customer_login_id'] = $customerSystem['customer_id'];
            // Create return information.
            $response = $this->makeResponseData($signInResult, $user, CodeDefinition::REDIRECT_TO_CUSTOMER_LAYOUT_MANY_MANAGER);
        }

        return $response;

    }

    /**
     * Log in at the customer branch.
     * @param $uid
     * @param $customerId
     * @param $customerBranchId
     * @return array|false
     */
    public function checkAuthWithBranch($uid, $customerId, $customerBranchId)
    {
        try {
            $signInResult = $this->fireBaseAuth->signInAsUser($uid);
        } catch (\Exception $e) {
            Log::error('Login user information does not exist.');
            Log::debug($e);
            return false;
        }
        $user = Auth::user();
        $role = $user->role;
        $errors = [];
        $user = $user->only(['user_id', 'login_id', 'role', 'customer_user_name', 'customer_user_name_kana', 'customer_login_id']);

        /** Get user information according to authority */
        $customerData = null;
        if ($role == CodeDefinition::ROLE_SUPER_USER) {
            $customer = new CuCustomerMultiDao();
            $customerData = $customer->getInforCustomer($customerId, $customerBranchId);
        } else {
            $cuUserBranchDao = new CuUserBranchDao();
            $customerData = $cuUserBranchDao->getInfoUserLoginWithBranch($user['user_id'], $customerId, $customerBranchId);
        }
        // Set return user information.
        if ($customerData != null && count($customerData->toArray()) == 0) {
            Log::error('Check authenticate with branch: No customer data. count($customerData)='. count($customerData->toArray()));
            $errors['statusCode'] = Response::HTTP_FORBIDDEN;
            return $errors;

        } else {
            $user['customer_id'] = $customerData[0]['customer_id'];
            $user['customer_branch_id'] = $customerData[0]['customer_branch_id'];
            $user['customer_user_id'] = $customerData[0]['customer_user_id'];
        }
        // Create return information.
        return $this->makeResponseData($signInResult, $user);
    }

    /**
     * User lock setting
     * @param $loginID
     * @return mixed
     */
    public function authLock( $loginID )
    {
        $cuUserDao = new CuUserDao;
        return $cuUserDao->updateLoginLock($loginID);
    }

    /**
     * Logout
     * @throws AuthException
     * @throws FirebaseException
     */
    public function logout()
    {
        $user = Auth::user();
        $this->fireBaseAuth->revokeRefreshTokens($user['uid']);
        Log::info('You have logged out the user by ID. $user->user_id='. $user->user_id);
        Auth::logout();
    }

    /**
     * Create return information.
     * @param $signInResult
     * @param $user
     * @param $page
     * @return array|object
     */
    protected function makeResponseData($signInResult, $user, $page = null) {
        $response['statusCode'] = Response::HTTP_OK;
        $response['data'] = $user;
        $response['access_token'] = $this->generateAccessKey($signInResult, $user);
        if ($page) {
            $response['page'] = $page;
        }
        return $response;
    }

    /**
     * Generate an access token
     * @param $signInResult
     * @param $user
     * @return string
     */
    protected function generateAccessKey($signInResult, $user)
    {
        $gcpToken = $signInResult->idToken();
        $payload = [
            'gcp_token' => $gcpToken,
            'user' => $user
        ];
        return JWT::encode($payload, config('auth.token_secret_key'), 'HS256');
    }

    /**
     * Get user information for authentication
     * @param $loginId
     * @param null $customerId
     * @return false
     */

    public function getUserForAuthentice($loginId, $customerId = null) {
        $arrayRole = [
            CodeDefinition::ROLE_SUPER_USER,
            CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR,
            CodeDefinition::ROLE_APPROVER,
            CodeDefinition::ROLE_ACCOUNTANT,
            CodeDefinition::ROLE_PERSON_IN_CHARGE,
            CodeDefinition::ROLE_NO_PERMISSION
        ];
        $userData =  $this->cuUserDao->getUserInfoByMaillAddress($loginId, $customerId);
        if ($userData && in_array($userData->role, $arrayRole)) return $userData;
        return false;
    }

    /**
     * Create a new one-time Token at access_flag = 0.
     * @param $loginId
     * @param $userId
     * @return \App\Models\CuToken|mixed
     */
    public function generateTokenLoginFirstTime($loginId, $userId, $newToken) {
        $cuToken = new CuTokenDao();
        $cuUserMultiDao = new CuUserMultiDao();
        $cuTokenUser = $cuUserMultiDao->getTokenByMaillAddress( $loginId );
        /** check exists One-time token. */
        if ( $cuTokenUser ) {
            Log::debug('Updated one-time token for first login. $userId='. $userId);
            return $cuToken->updateNewToken( $userId, $newToken );
        } else {
            /** Create a new one-time token for the first login. */
            Log::debug('Create a new one-time password for the first login. $userId='. $userId);
            return $cuToken->createNewToken( $userId, $newToken );
        }
    }

    /**
     * Generate an access token for the first login
     * @return array|false|object
     */
    public function generateAccessTokenLoginFirstTime ()
    {
        $signInResult = null;
        $credentials = request(['login_id', 'password']);
        /** Authenticate user information on google identity platform.*/
        $credentials['status'] = DaoConstants::STATUS_ACTIVE;
        $loginId = $credentials['login_id'];
        try {
            $signInResult = $this->fireBaseAuth->signInWithEmailAndPassword($loginId, $credentials['password']);
        } catch (\Exception $e) {
            Log::error('Incorrect login information on google identity platform by email. $loginId='. $this->removeEmailDomain($loginId));
            return false;
        }
        /** Get user information by login_id for authentication */
        $user = $this->getUserForAuthentice($loginId);
        if (!$user){
            Log::error('There is no user matching the login ID. $user=NULL');
            return false;
        }

        $user = $user->only(['user_id', 'customer_id', 'role', 'customer_user_name', 'customer_user_name_kana']);

        /** Log in user by ID*/
        Auth::loginUsingId($user['user_id']);

        /** Get branch information by login_id */
        $customerData = $this->cuUserMultiDao->getInfoUserBranchForAuthenticate($loginId);
        if (empty($customerData) || count($customerData) == 0) {
            Log::error('No branch matches login ID. count($customerData)='. count($customerData));
            return false;
        }

        $countCustomerUser = count($customerData);
        if ($countCustomerUser === DaoConstants::CU_CUSTOMER_NO_MANAGER) {
            Log::error('There is no branch that matches the user information. $countCustomerUser='. $countCustomerUser);
            return false;

        } else if ($countCustomerUser === DaoConstants::CU_CUSTOMER_HAS_ONE_MANAGER) {
            Log::debug('There is only one branch where the user is logged in. $countCustomerUser='. $countCustomerUser);
            $user['customer_id'] = $customerData[0]['customer_id'];
            $user['customer_branch_id'] = $customerData[0]['customer_branch_id'];
            $user['customer_user_id'] = $customerData[0]['customer_user_id'];
            $user['customer_login_id'] = $customerData[0]['customer_id'];
            // Create return information.
            $response = $this->makeResponseData($signInResult, $user);

        } else {
            Log::debug('The login user has many branches. $countCustomerUser='. $countCustomerUser);
            $user['customer_id'] = null;
            $user['customer_branch_id'] = null;
            $user['customer_user_id'] = null;
            $user['customer_login_id'] = null;
            // Create return information.
            $response = $this->makeResponseData($signInResult, $user);
        }
        return $response;
    }

    /**
     * Check customer system usage with customer_login_id.
     * @param $customerLoginId
     * @param $systemUsageValidateType
     * @return false
     */
    public function validateSystemUsageStatus( $customerLoginId, $systemUsageValidateType )
    {
        if ($systemUsageValidateType == self::SYSTEM_USAGE_VALIDATE_LOGIN) {
            $customerSystem = $this->cuCustomerOptionDao->getCustomerSystemStatus($customerLoginId);
        } else {
            $customerSystem = $this->cuCustomerOptionDao->getCustomerSystemStatusByCustomerId($customerLoginId);
        }
        if (!$customerSystem) {
            Log::error('It is customer_login_id ($customerLoginId='.$customerLoginId.') and does not exist in the customer system. $customerSystem=NULL');
            return false;
        }

        if ($customerSystem['status'] == DaoConstants::STATUS_INACTIVE) {
            Log::error('This customer system is not working. $customerLoginId= '. $customerLoginId .' $customerSystem->status='. $customerSystem->status);
            return false;
        }

        if ($customerSystem['user_lock']) {
            Log::error('This customer system is locked. $customerLoginId= '. $customerLoginId .' $customerSystem->user_lock='. $customerSystem->user_lock);
            return false;
        }

        if ($customerSystem['plan_type'] == DaoConstants::CU_CUSTOMER_OPTION_PLAN_TYPE_NOT_USE) {
            Log::error('the plan_type of the system you are not using. $customerLoginId= '. $customerLoginId .' $customerSystem->plan_type='. $customerSystem->plan_type);
            return false;
        }

        /** Check the expiration date of the customer system. */
        $startDate = new Carbon($customerSystem['start_date']);
        $endDate = $customerSystem['end_date'];
        if (!empty($customerSystem['end_date'])) {
            $endDate = new Carbon($customerSystem['end_date']);
        }
        $currentDate = Carbon::now();
        if (!($currentDate->greaterThanOrEqualTo($startDate) && (empty($endDate) || $endDate->greaterThanOrEqualTo($currentDate)))) {
            Log::error('hahaha');
            Log::error('Customer system status expiration date is invalid. $customerLoginId= '. $customerLoginId .', currentDate='. $currentDate .', $startDate='. $startDate .', $endDate='. $endDate);
            return false;
        }
        Log::debug('Customer system status is valid');

        return $customerSystem;
    }
}
