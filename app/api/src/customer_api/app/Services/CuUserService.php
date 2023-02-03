<?php

namespace App\Services;

use App\Common\CodeDefinition;
use App\Common\CommonHelper;
use App\Dao\DaoConstants;
use App\Dao\MultiTable\CuUserMultiDao;
use App\Dao\SingleTable\CuCustomerBranchDao;
use App\Dao\SingleTable\CuCustomerDao;
use App\Dao\SingleTable\CuCustomerOptionDao;
use App\Dao\SingleTable\CuCustomerUserDao;
use App\Dao\SingleTable\CuTokenDao;
use App\Dao\SingleTable\CuUserBranchDao;
use App\Dao\SingleTable\CuUserDao;
use App\Http\Requests\Request;
use App\Jobs\ImportUserCsv;
use App\Mail\ActivateUserEmail;
use App\Mail\ImportCsvResultEmail;
use App\Mail\UpdatePasswordEmail;
use App\Mail\UpdateUserImportNotLogin;
use App\Models\CuUserBranch;
use App\Services\Interfaces\GoogleStorageFileServiceInterface;
use App\Services\Service as BaseService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Auth as FireBaseAuth;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class CuUserService extends BaseService
{
    const VALUE_CSV_BOOLEAN = ["true", "TRUE", "false", "FALSE"];
    const VALUE_CSV_ROLE = [
        CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR,
        CodeDefinition::ROLE_APPROVER,
        CodeDefinition::ROLE_ACCOUNTANT,
        CodeDefinition::ROLE_PERSON_IN_CHARGE,
        CodeDefinition::ROLE_NO_PERMISSION
    ];
    const VALUE_CSV_BELONG_ACTIVE = '所属する';
    const VALUE_CSV_BELONG_INACTIVE ='所属しない';
    const VALUE_CSV_PROCESS_TYPE_ADD ='追加';
    const VALUE_CSV_PROCESS_TYPE_DELETE ='削除';
    const VALUE_CSV_USER_LOCK = ["0","1"];
    const VALUE_ERROR_IMPORT_DEFAULT = 'E400034';
    /**
     * @var CuUserMultiDao
     */
    protected CuUserMultiDao $cuUserMultiDao;
    /**
     * @var FireBaseAuth
     */
    protected FireBaseAuth $fireBaseAuth;
    /**
     * @var AuthService
     */
    protected AuthService $authService;
    /**
     * @var CuFileService
     */
    protected CuFileService $cuFileService;
    /**
     * @var GoogleStorageFileServiceInterface
     */
    protected GoogleStorageFileServiceInterface $googleStorageFileService;

    protected $commonHelper;

    /**
     * CuUserService constructor.
     * @param CuUserMultiDao $cuUserMultiDao
     * @param FireBaseAuth $fireBaseAuth
     * @param AuthService $authService
     * @param CuFileService $cuFileService
     * @param GoogleStorageFileServiceInterface $googleStorageFileService
     */
    public function __construct(
        CuUserMultiDao $cuUserMultiDao,
        FireBaseAuth $fireBaseAuth,
        AuthService $authService,
        CuFileService $cuFileService,
        GoogleStorageFileServiceInterface $googleStorageFileService,
        CommonHelper $commonHelper
    )
    {
        $this->cuUserMultiDao = $cuUserMultiDao;
        $this->fireBaseAuth = $fireBaseAuth;
        $this->authService = $authService;
        $this->cuFileService = $cuFileService;
        $this->googleStorageFileService = $googleStorageFileService;
        $this->commonHelper = $commonHelper;
    }

    /**
     * resetting password
     * @param $request
     * @return mixed
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updatePasswordResetting( $request )
    {
        try {
            $cuUser = new CuUserDao();
            $cuToken = new CuTokenDao();
            $cuTokenUser = $cuToken->getInfoByToken( $request->token );
            if ( $cuTokenUser ) {
                $userRecord = $cuUser->getUserByUserId( $cuTokenUser->user_id );
                $loginId = $userRecord->login_id;
                $userId = $cuTokenUser->user_id;
                $accessFlag = $userRecord->access_flg;
                $passwordEncrypt = $this->commonHelper->encryptData($request->password);

                /** Change the password on the current server. */
                $cuUser->updatePassword( $userId, $passwordEncrypt, $accessFlag );

                /** Check the account information with login_id on the google identity platform.*/
                $userRecordGcs = $this->fireBaseAuth->getUserByEmail($loginId);
                if (!$userRecordGcs) {
                    Log::error('An account does not exist on the google identity platform. $userRecordGcs='. $userRecordGcs);
                    return false;
                }

                Log::debug('An account exists on the google identity platform.');

                $this->fireBaseAuth->changeUserPassword($userRecordGcs->uid, $request->password);
                Log::debug('You have successfully changed your password on the google identity platform.');


                /** Removed token from the cu_token table. */
                $cuToken->deleteToken( $request->token );

                /** Returns access_token in response */
                $request->merge([
                    'login_id' => $loginId,
                    'password' => $request->password
                ]);
                Log::debug('The password has been updated successfully.');
                return $this->authService->generateAccessTokenLoginFirstTime();
            }

            Log::debug('Token does not exist or is invalid. $request->token='. $request->token);
            return false;
        } catch (\Exception $e){
            Log::error('An exception error has occurred. When reseting password');
            Log::error($e);
            return false;
        }
    }

    /**
     * First password update.
     * @param $request
     * @return mixed
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updatePasswordFirstTime( $request )
    {
        try {
            $cuUser = new CuUserDao();
            $cuToken = new CuTokenDao();
            $cuTokenUser = $cuToken->getInfoByToken( $request->token );
            if ( $cuTokenUser ) {
                $userRecord = $cuUser->getUserByUserId( $cuTokenUser->user_id );
                $loginId = $userRecord->login_id;
                $userId = $cuTokenUser->user_id;
                $accessFlag = $userRecord->access_flg;
                $passwordEncrypt = $this->commonHelper->encryptData($request->password);
                /** Check the account information with login_id on the google identity platform.*/
                $userRecordGcs = $this->fireBaseAuth->getUserByEmail($loginId);
                if (!$userRecordGcs) {
                    Log::error('The account does not exist on the google identity platform. $userRecordGcs='. $userRecordGcs);
                    return false;
                }
                Log::debug('An account exists on the google identity platform.');
                /**Update password on google identity platform. */
                $this->fireBaseAuth->changeUserPassword($userRecordGcs->uid, $request->password);
                Log::debug('You have successfully changed your password on the google identity platform.');

                /** Change the password on the current server. */
                $cuUser->updatePassword( $userId, $passwordEncrypt, $accessFlag );

                /** Removed token from the cu_token table.*/
                $cuToken->deleteToken( $request->token );

                /** Returns access_token. */
                $request->merge([
                    'login_id' => $loginId,
                    'password' => $request->password
                ]);
                Log::debug('The first time password update was successful.');
                return $this->authService->generateAccessTokenLoginFirstTime();
            }

            Log::error('Failed to update the password for the first time. $request->token='. $request->token );
            return false;
        } catch (\Exception $e){
            Log::debug('An exception error has occurred. When update password firstime');
            Log::error($e);
            return false;
        }
    }

    /**
     * modified password
     * @param $request
     * @param $userId
     * @return array|false|object
     * @throws AuthException
     * @throws FirebaseException
     */
    public function changePassword ( $request, $userId )
    {
        $oldOriginPassword = $request->password;
        $cuUser = new CuUserDao();
        $cuUserRecord = $cuUser->getUserByUserId($userId);
        if ($cuUserRecord && ($oldOriginPassword == $this->commonHelper->decryptData($cuUserRecord->password))) {
            $passwordEncrypt = $this->commonHelper->encryptData($request->new_password);
            /** Update password cu_user */
            $cuUserUpdate = $cuUser->updateNewPassword($userId, $passwordEncrypt);
            if (!$cuUserUpdate) {
                Log::debug('Change password failed');
                return false;
            }
            try {
                Log::debug('Change account password on google identity platform.');
                $this->fireBaseAuth->changeUserPassword(Auth::user()->uid, $request->new_password);
            } catch (\Exception $e) {
                Log::error($e);
            }
            $request->merge([
                'login_id' => $cuUserRecord->login_id,
                'password' => $request->new_password
            ]);
            return $this->authService->generateAccessTokenLoginFirstTime();
        }
        Log::debug('Password update failed.$cuUserRecord='. json_encode($cuUserRecord, JSON_UNESCAPED_UNICODE));
        return false;
    }

    /**
     * Get the user information list.
     * @param $request
     * @param $customerID
     * @return mixed
     */
    public function getListUser ( $request, $customerID )
    {
        $cuUserMulti = new CuUserMultiDao();
        $pageVolume = $request->page_volume;
        $nextPage = $request->next_page;
        if (empty($pageVolume)) {
            $pageVolume = CodeDefinition::PAGINATE_DEFAULT_LIMIT;
        }
        if (empty($nextPage)) {
            $nextPage = CodeDefinition::PAGINATE_DEFAULT_PAGE;
        }
        $branchName = $request->branch_name;
        $userName = $request->user_name;
        $sort = $this->convertSortConditionToColumnQueryUserList($request->get('sort'));
        return $cuUserMulti->getListUser($branchName, $userName, $request->role_id, $customerID, $nextPage, $pageVolume, $sort);
    }

    /**
     * Get user details.
     * @param $userId
     * @return false|Builder|Model|object|null
     */
    public function getDetailUser ( $userId )
    {
        $customerId = Auth::user()->customer_id;
        $cuUserMulti = new CuUserMultiDao();

        $recordUser = $cuUserMulti->getDetailUser($userId, $customerId);
        if ($recordUser) {
            $customerBranchName = $recordUser['customer_branch_name'];
            $customerBranchId = $recordUser['customer_branch_id'];
            if (!empty($customerBranchName) && !empty($customerBranchId)) {
                $arrCustomerBranchName = explode(',', $customerBranchName);
                $arrCustomerBranchId = explode(',', $customerBranchId);
                $recordUser['customer_branch_name'] = $arrCustomerBranchName;
                $recordUser['customer_branch_id'] = $arrCustomerBranchId;
                return $recordUser;
            }
        }
        return false;
    }

    /**
     * Count the total users.
     * @param $request
     * @param $customerID
     * @return Builder[]|Collection
     */
    public function getCountUser($request, $customerID) {
        $cuUserMulti = new CuUserMultiDao();
        $branchName = $request->get('branch_name');
        $userName = $request->get('user_name');
        $result = $cuUserMulti->getTotalUser($branchName, $userName, $request->role_id, $customerID);
        if ($result) {
            return $result[0]->cnt;
        }
        return 0;
    }

    /** */
    protected function convert($field) {
        return mb_convert_encoding($field, 'UTF-8', 'SJIS');
    }


    /**
     * import CSV data
     * @param $request
     * @return false|array
     */
    public function importCsvData($request)
    {
        $userId = Auth::user()->user_id;
        $customerId = Auth::user()->customer_id;
        $customerBranchId = Auth::user()->customer_branch_id;
        $customerUserName = Auth::user()->customer_user_name;
        $userRecord  = new CuUserDao();
        $userData = $userRecord->getUserByUserId($userId);
        $loginId = $userData['login_id'];
        /** Upload file gcp server */
        if ($request->hasFile('file')) {
            $fileUpload = $this->cuFileService->uploadFile($request->file('file'));
            if ($fileUpload && $fileUpload['success']) {
                Log::debug('You have successfully uploaded the user list file to GCS.');
                Log::debug('Call the command to register user information in DB.');
                dispatch(new ImportUserCsv($fileUpload['file_path'], $fileUpload['origin_name'], $userId, $customerId, $customerBranchId, $customerUserName, $loginId));
                return true;
            } else {
                Log::error('Failed to upload user list file to GCS. $fileUpload='. json_encode($fileUpload, JSON_UNESCAPED_UNICODE));
                return [
                    "errorcode" => "E400018",
                    "message" => "CSVファイルのフォーマットに問題があります。"
                ];
            }
        }
        return false;
    }

    /**
     * Variation user information before importing.
     *
     * @param $filePath
     * @param $fileName
     * @param $systemUserId
     * @param $customerId
     * @param $customerBranchId
     * @param $customerUserName
     * @param $userLoginId
     * @return bool
     * @throws AuthException
     * @throws FirebaseException
     */
    public function proccessImport($filePath, $fileName, $systemUserId, $customerId, $customerBranchId, $customerUserName, $userLoginId)
    {
        Log::debug('Job import CSV : start job');
        $message_error ='';
        $dataErrors= [];
        $countItem = 0;
        $countItemResults = 0;
        $countResultSuccess = 0;
        /**Get CSV file information on the storage google cloud server. */
        $fileObject = $this->cuFileService->getFileData($filePath);
        /** Read line by line in the CSV file. */
        if (is_array($fileObject) && !$fileObject['success']) {
            /** @var $errorcode = E400031 */
            $message_error = "CSVファイルの取得に失敗しました。改めて CSVファイルを登録してください。";
            Log::error('Job import CSV : ' . $message_error);
        }
        if (empty($message_error) && array_key_exists('data', $fileObject)) {
            $fileBody = self::csvstring_to_array($fileObject['data']);
            if (!empty($fileBody['error'])) {
                $dataErrors = $fileBody['error'];
                /** @var $errorcode = E400032 */
                $message_error = "CSVファイルのフォーマットに誤りがあります。CSVファイルの内容を修正して、改めて登録してください。";
                Log::error('Job import CSV : ' . $message_error);
            }
        }

        /** Get customer information by customer_id. */
        $cuCustomer = new CuCustomerDao();
        $cuCustomer = $cuCustomer->getCustomerName($customerId);
        $customerName =  $cuCustomer->customer_name ?: '';
        Log::debug('Job import CSV : get customer information');

        /** Get customer branch information by customer_branch_id.*/
        $cuCustomerBranch = new CuCustomerBranchDao();
        $cuCustomerBranchName = $cuCustomerBranch->getCustomerBranchName([$customerBranchId]);
        $cuCustomerBranchName =  $cuCustomerBranchName->customer_branch_name ?: '';
        Log::debug('Job import CSV : get customer branch information');

        $inValid = false;
        if(empty($message_error)) {
            DB::beginTransaction();
            Log::debug('Job import CSV : start transaction');
            try {
                if (count($fileBody['data']) > 0) {
                    $dataUniques = [];
                    /** validate content*/
                    foreach ($fileBody['data'] as $key => $item) {
                        $indexLog = $key + 2;
                        // Variation of login_id (required, email, max: 255, unique)
                        if (empty($item['login_id'])) {
                            $inValid = true;
                            $dataErrors[$indexLog]['login_id'] =  $indexLog . "行目login_idは必須項目です。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['login_id']);
                        } else if (!preg_match(Request::REGEX_EMAIL, $item['login_id'])) {
                            $inValid = true;
                            $dataErrors[$indexLog]['login_id'] =  $indexLog . "行目login_idは有効なメールアドレスを入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['login_id']);
                        } else if(strlen($item['login_id']) > 255) {
                            $inValid = true;
                            $dataErrors[$indexLog]['login_id'] =  $indexLog . "行目login_idは255文字以下で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['login_id']);
                        } else if (in_array($item['login_id'].'_'.$item['customer_id'], $dataUniques)) {
                            $inValid = true;
                            $dataErrors[$indexLog]['login_id'] =  $indexLog . "行目使用されているメールアドレスです。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['login_id']);
                        } else {
                            $dataUniques[] = $item['login_id'].'_'.$item['customer_id'];
                        }

                        // Variation of belong (belong in ('belongs','does not belong'))
                        if (empty($item['belong']) ||
                            (!empty($item['belong']) && !in_array($item['belong'], [ self::VALUE_CSV_BELONG_ACTIVE, self::VALUE_CSV_BELONG_INACTIVE ], true))
                        )
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['belong'] =  $indexLog . "行目指定されたbelongは存在しません。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['belong']);
                        }

                        // Variation of user_lock (is_number)
                        if ($item['user_lock'] == null || trim($item['user_lock']) == "" || !in_array($item['user_lock'], self:: VALUE_CSV_USER_LOCK, true))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['user_lock'] =  ($indexLog) . "行目user_lockのデータ型が不正です。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['user_lock']);
                        }

                        // Variation of customer_user_name (required, max:255)
                        if (empty($item['customer_user_name']))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_name'] =  $indexLog . "行目customer_user_nameは必須項目です。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_name']);
                        } else if (mb_strlen($item['customer_user_name'], 'UTF-8') > 255) {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_name'] =  $indexLog . "行目customer_user_nameは255文字以下で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_name']);
                        }

                        // Variation of customer_user_name_kana(required, max:255, regex: katakana)
                        if (empty($item['customer_user_name_kana'])) {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_name_kana'] =  $indexLog . "行目customer_user_name_kanaは必須項目です。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_name_kana']);
                        } else if (mb_strlen($item['customer_user_name_kana'], 'UTF-8') > 255) {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_name_kana'] =  $indexLog . "行目customer_user_name_kanaは255文字以下で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_name_kana']);
                        } else if (!preg_match(Request::REGEX_KATAKANA, $item['customer_user_name_kana'])) {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_name_kana'] =  $indexLog . "行目customer_user_name_kanaはカナで入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_name_kana']);
                        }

                        // Variation of customer_user_tel (required_if, mobile_phone) (format 01011110000)
                        if (empty($item['customer_user_tel'])
                            || (!empty($item['customer_user_tel'])
                                && !preg_match(Request::REGEX_PHONE_NUMBER, $item['customer_user_tel'])
                            ))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_user_tel'] =  $indexLog . "行目customer_user_telは10桁か11桁の数字で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_user_tel']);
                        }

                        // Variation of role(is_numeric) and in array [ 1,2,3,4,9 ]
                        if (empty($item['role']) || !in_array($item['role'], self::VALUE_CSV_ROLE))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['role'] =  $indexLog . "行目有効なroleではありません。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['role']);
                        }

                        // Variation of customer_id(is_integer)
                        if (empty($item['customer_id'])
                            || (!empty($item['customer_id']) && !preg_match(Request::REGEX_INT, $item['customer_id']))
                        )
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_id'] =  $indexLog . "行目customer_idは半角数字で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_id']);
                        }

                        // Variation of customer_branch_id (is_integer)
                        if (empty($item['customer_branch_id']) || (!empty($item['customer_branch_id']) && !preg_match(Request::REGEX_INT, $item['customer_branch_id'])))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['customer_branch_id'] =  $indexLog . "行目customer_branch_idは半角数字で入力してください。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['customer_branch_id']);
                        }

                        // Variation of process_type(is_integer)
                        if (empty($item['process_type'])
                            || (!empty($item['process_type'])
                                && !in_array($item['process_type'], [ self::VALUE_CSV_PROCESS_TYPE_DELETE, self::VALUE_CSV_PROCESS_TYPE_ADD ])
                            ))
                        {
                            $inValid = true;
                            $dataErrors[$indexLog]['process_type'] =  $indexLog . "行目process_typeの値が不正です。";
                            Log::error('Job import CSV : ' . $dataErrors[$indexLog]['process_type']);
                        }
                        $countItem ++;

                    }
                    /** Send email fail validate */
                    if ($inValid) {
                        DB::rollBack();
                        /** @var  $message_error_id = E400033 */
                        $message_error = "CSVファイルの入力値に誤りがあります。CSVファイルの内容を修正して、改めて登録してください。";
                        return $this->sendEmailResultImport(
                            $userLoginId,
                            $customerName,
                            $cuCustomerBranchName,
                            $customerUserName,
                            $fileName,
                            $countItem,
                            $countItemResults,
                            $message_error,
                            $dataErrors
                        );
                    }
                    /** Check customer_id */
                    foreach ($fileBody['data'] as $key => $item) {
                        if ($item['customer_id'] != $customerId) {
                            $inValid = true;
                            /** @var  $message_error_id = E400029 */
                            $message_error = "有効なcustomer_idではありません。";
                            $dataErrors[$key + 2] =  ($key + 2) . '行目' . $message_error;
                            Log::error('Job import CSV : ' . ($key + 2) . '行目' .  $message_error);
                        }
                    }
                    /** Send email result */
                    if ($inValid) {
                        DB::rollBack();
                        return $this->sendEmailResultImport(
                            $userLoginId,
                            $customerName,
                            $cuCustomerBranchName,
                            $customerUserName,
                            $fileName,
                            $countItem,
                            $countItemResults,
                            $message_error,
                            $dataErrors
                        );
                    }
                    /** Check customer_branch_id */
                    try {
                        $dataCustomerBranch = $cuCustomerBranch->getCustomerBranchId($customerId);
                        if ($dataCustomerBranch && count($dataCustomerBranch) > 0) {
                            foreach ($dataCustomerBranch as $customerBranchItem) {
                                $arrDataCustomerBranch[] = $customerBranchItem['customer_branch_id'];
                            }
                        }
                    } catch (\Exception $exception) {
                        $dataCustomerBranch = false;
                        Log::error('Job import CSV : '.  $exception);
                    }
                    /** Get data customer branch */
                    if (!$dataCustomerBranch) {
                        DB::rollBack();
                        /** @var  $message_error_id = E400034 */
                        $message_error = "顧客支店ID取得に失敗しました。システム管理者にお問い合わせください。";
                        $dataErrors= [];
                        Log::error('Job import CSV : '.  $message_error);
                        return $this->sendEmailResultImport(
                            $userLoginId,
                            $customerName,
                            $cuCustomerBranchName,
                            $customerUserName,
                            $fileName,
                            $countItem,
                            $countItemResults,
                            $message_error,
                            $dataErrors
                        );
                    } else {
                        foreach ($fileBody['data'] as $index => $row) {
                            if (!in_array($row['customer_branch_id'], $arrDataCustomerBranch)) {
                                $inValid = true;
                                /** @var  $message_error_id = E400029 */
                                $message_error = "有効なcustomer_branch_idではありません。";
                                $dataErrors[$index + 2] =  ($index + 2) . '行目' . $message_error;
                                Log::error('Job import CSV : ' . ($index + 2) . '行目' .  $message_error);
                            }
                        }
                        /** Send email result */
                        if ($inValid) {
                            DB::rollBack();
                            return $this->sendEmailResultImport(
                                $userLoginId,
                                $customerName,
                                $cuCustomerBranchName,
                                $customerUserName,
                                $fileName,
                                $countItem,
                                $countItemResults,
                                $message_error,
                                $dataErrors
                            );
                        }
                    }
                    /** Check cu_user_branch */
                    foreach ($fileBody['data'] as $index => $itemRow ) {
                        if ($itemRow['process_type'] == self::VALUE_CSV_PROCESS_TYPE_DELETE) {
                            try {
                                $dataUserBranchMultiDao = new CuUserMultiDao();
                                $dataUserBranch = $dataUserBranchMultiDao->getUserBranch($itemRow['login_id'], [$itemRow['customer_branch_id']]);
                            } catch (\Exception $exception) {
                                $dataUserBranch = false;
                                Log::error('Job import CSV : '.  $exception);
                            }
                            if (!$dataUserBranch) {
                                $inValid = true;
                                /** @var  $message_error_id = E400034 */
                                $message_error = "顧客支店ID取得に失敗しました。システム管理者にお問い合わせください。";
                                $dataErrors[$index + 2] =  ($index + 2) . '行目' . $message_error;
                                Log::error('Job import CSV : ' . ($index + 2) . '行目' .  $message_error);
                            } else if ( $dataUserBranch[0]->cnt <= 0 ) {
                                $inValid = true;
                                /** @var  $message_error_id = E400038 */
                                $message_error = "所属支店を全て削除することは出来ません。customer_branch_id及びprocess_typeをご確認ください。";
                                $dataErrors[$index + 2] =  ($index + 2) . '行目' . $message_error;
                                Log::error('Job import CSV : ' . ($index + 2) . '行目' .  $message_error);
                            }

                        }
                    }
                    /** Send email result */
                    if ($inValid) {
                        DB::rollBack();
                        return $this->sendEmailResultImport(
                            $userLoginId,
                            $customerName,
                            $cuCustomerBranchName,
                            $customerUserName,
                            $fileName,
                            $countItem,
                            $countItemResults,
                            $message_error,
                            $dataErrors
                        );
                    }

                    /** Validate system owner */
                    try {
                        $cuUser = new CuCustomerOptionDao();
                        $systemOwner = $cuUser->getCustomerSystermOwner($customerId);
                    } catch (\Exception $exception) {
                        $systemOwner = false;
                        Log::error('Job import CSV : '.  $exception);
                    }
                    if (!$systemOwner) {
                        DB::rollBack();
                        /** $message_error_id = E400034 */
                        $message_error = "システムオーナー取得に失敗しました。システム管理者にお問い合わせください。";
                        $dataErrors= [];
                        Log::error('Job import CSV : '.  $message_error);
                        return $this->sendEmailResultImport(
                            $userLoginId,
                            $customerName,
                            $cuCustomerBranchName,
                            $customerUserName,
                            $fileName,
                            $countItem,
                            $countItemResults,
                            $message_error,
                            $dataErrors
                        );
                    }
                    $systemOwnerId = $systemOwner->admin_user_id;
                    /** Process */
                    foreach ($fileBody['data'] as $key => $item) {
                        $indexLog = $key + 2;
                        $resultCreate = $resultUpdate = false;
                        Log::debug('Job import CSV : check user information on gcp server on line ' . $indexLog );
                        $recordCuUser = self::getUserCsvByEmailAddress($item['login_id']);

                        if (!$recordCuUser) {
                            /** Validate system owner */
                            try {
                                $resultCreate = self::createUserCSV($item, $systemUserId, $item['customer_id'], $item['customer_branch_id']);
                                if (is_array($resultCreate) && $resultCreate['error'] == self::VALUE_ERROR_IMPORT_DEFAULT) {
                                    $message_error = $resultCreate['message_error'];
                                } else {
                                    Log::debug('Job import CSV : Create a new user with the information on line ' . $indexLog );
                                }
                            } catch (\Exception $exception) {
                                Log::error('Job import CSV : '.  $exception);
                            }
                        } else {
                            $userIdRecord = $recordCuUser->user_id;
                            /** Check systemOwner */
                            if ($systemOwnerId == $userIdRecord && $item['role'] != CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR) {
                                DB::rollBack();
                                /** $message_error_id = E400022 */
                                $message_error = "システムオーナーのロールの変更は出来ません。";
                                Log::error('Job import CSV : '.  $message_error);
                                return $this->sendEmailResultImport(
                                    $userLoginId,
                                    $customerName,
                                    $cuCustomerBranchName,
                                    $customerUserName,
                                    $fileName,
                                    $countItem,
                                    $countItemResults,
                                    $message_error,
                                    $dataErrors
                                );
                            } else if ($systemOwnerId == $userIdRecord && $item['user_lock'] == DaoConstants::CU_USER_LOCKED) {
                                DB::rollBack();
                                /** $message_error_id = E400030 */
                                $message_error = "システムオーナーをロックすることは出来ません。";
                                Log::error('Job import CSV : '.  $message_error);
                                return $this->sendEmailResultImport(
                                    $userLoginId,
                                    $customerName,
                                    $cuCustomerBranchName,
                                    $customerUserName,
                                    $fileName,
                                    $countItem,
                                    $countItemResults,
                                    $message_error,
                                    $dataErrors
                                );
                            }
                            try {
                                $resultUpdate = self::updateUserDataCsv($item, $systemUserId, $item['customer_id'], $item['customer_branch_id'], $recordCuUser);
                                if (is_array($resultUpdate) && $resultUpdate['error'] == self::VALUE_ERROR_IMPORT_DEFAULT) {
                                    $message_error = $resultUpdate['message_error'];
                                } else {
                                    Log::error('Job import CSV : Update user information with information on line ' . $indexLog);
                                }
                            } catch (\Exception $exception) {
                                Log::error('Job import CSV : '.  $exception);
                            }
                        }

                        if (empty($message_error) && ($resultCreate || $resultUpdate)) {
                            $countResultSuccess ++;
                            Log::error('Job import CSV : increase the record counter variable to satisfy the condition that data can be entered on line ' . ($key + 1) );
                        } else {
                            DB::rollBack();
                            /** $message_error_id = E400034 */
                            if (empty($message_error)) {
                                $message_error = "ユーザー情報登録に失敗しました。システム管理者にお問い合わせください。";
                            }
                            $dataErrors[$indexLog] =  $indexLog . "行目" . $message_error;
                            Log::error('Job import CSV : '.  $indexLog . "行目". $message_error);
                            return $this->sendEmailResultImport(
                                $userLoginId,
                                $customerName,
                                $cuCustomerBranchName,
                                $customerUserName,
                                $fileName,
                                $countItem,
                                $countItemResults,
                                $message_error,
                                $dataErrors
                            );
                        }
                    }
                }
                if (!$inValid) {
                    Log::debug('Job import CSV : make data commit');
                    $countItemResults = $countResultSuccess;
                    DB::commit();
                } else {
                    DB::rollBack();
                    /** $message_error_id = E400034 */
                    $message_error = "ユーザー情報登録に失敗しました。システム管理者にお問い合わせください。";
                    Log::error('Job import CSV : '.  $message_error);
                }
            } catch (\Exception $exception) {
                Log::error($exception);
                DB::rollBack();
                /** $message_error_id = E400034 */
                $message_error = "ユーザー情報登録に失敗しました。システム管理者にお問い合わせください。";
                Log::error( 'Job import CSV : rollback data due to an error is ' . $exception->getMessage() . '----Line----' . $exception->getLine());
            }
        } else {
            $countItem = count($dataErrors);
        }
        return $this->sendEmailResultImport(
            $userLoginId,
            $customerName,
            $cuCustomerBranchName,
            $customerUserName,
            $fileName,
            $countItem,
            $countItemResults,
            $message_error,
            $dataErrors
        );
    }

    /**
     * Send email result import
     * @param $userLoginId
     * @param $customerName
     * @param $cuCustomerBranchName
     * @param $customerUserName
     * @param $fileName
     * @param $countItem
     * @param $countItemResults
     * @param $message_error
     * @param $dataErrors
     * @return false|mixed
     */
    function sendEmailResultImport($userLoginId, $customerName, $cuCustomerBranchName, $customerUserName, $fileName, $countItem, $countItemResults, $message_error, $dataErrors) {
        /** Register an email sending job to the system administrator. */
        try {
            /** replace character in email @var $loginId */
            $userLoginIdWriteLog = $this->removeEmailDomain($userLoginId);
            $countErrors = count($dataErrors);
            Log::debug('Job import CSV : Job registration email import results to . '. $userLoginIdWriteLog );
            Mail::to($userLoginId)->send(
                new ImportCsvResultEmail(
                    $customerName,
                    $cuCustomerBranchName,
                    $customerUserName,
                    $fileName,
                    $countItem,
                    $countItemResults,
                    $message_error,
                    $dataErrors,
                    $countErrors
                )
            );
        } catch (\Exception $exception) {
            Log::error($exception);
            Log::error( "Job import CSV : job registration error email import result is " . $exception->getMessage() . '----Line----' . $exception->getLine());
        }
    }


    /**
     * Convert CSV file to array.
     * @param $fileBody
     * @return array|string|string[]
     */
    public function csvstring_to_array($fileBody) {
        $dataReturn = [];
        $dataErrors = [];
        $delimiter = empty($options['delimiter']) ? "," : $options['delimiter'];
        $to_object = empty($options['to_object']) ? false : true;
        $lines = explode("\r\n", $fileBody);
        $field_names = explode($delimiter, array_shift($lines));
        foreach ($lines as $lineIndex => $line) {
            $indexLog = $lineIndex + 2;
            // Skip empty lines.
            if (empty($line)) continue;
            $fields = explode($delimiter, $line);
            $_res = $to_object ? new stdClass : array();
            if (count($fields) != count(CodeDefinition::HEADER_CSV_FILE)) {
                /** @var  $message_error_id = E400032 */
                $message_error = "CSVファイルのフォーマットに誤りがあります。CSVファイルの内容を修正して、改めて登録してください。";
                Log::error('Job import CSV : ' . $indexLog . '行目 ' . $message_error);
                $dataErrors[$indexLog] = $indexLog . '行目 ' . $message_error ;
            }
            foreach ($field_names as $key => $f) {
                if (is_array($fields) && array_key_exists($key, $fields)) {
                    if ($to_object) {
                        $_res->{$f} = self::convert(filter_var(trim($fields[$key]), FILTER_SANITIZE_STRING));
                    } else {
                        $_res[$f] = self::convert(filter_var(trim($fields[$key]), FILTER_SANITIZE_STRING));
                    }
                } else {
                    Log::error('Job import CSV : there was an error in field '  . $f . ' line '. ($lineIndex + 1 ));
                }
            }

            $res[] = $_res;
        }
        $dataReturn['data'] = $res;
        $dataReturn['error'] = $dataErrors;
        return $dataReturn;
    }

    /**
     * register new user
     * @param $request
     * @return array|bool
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createUser( $request )
    {
        try {
            $customerId = Auth::user()->customer_id;
            $userLogin = Auth::user()->user_id;
            $loginId = $request->customer_user_email;
            $cuUser = new CuUserDao();
            $recordGcp = $cuUserRecord = false;
            /** General new password */
            $originalPassword = $this->registerNewPassword();
            $encryptPassword = $this->getEncryptPassword($originalPassword);
            if (!$encryptPassword) {
                Log::debug('Create new password failed');
                return false;
            }
            /** Get user information by login_id. */
            $recordCuUser = $this->getUserByEmailAddress($loginId);
            if ($recordCuUser && $recordCuUser['access_flg'] == DaoConstants::CU_USER_ACCESS_FLAG) {
                /** Set a default password. */
                $cuUserRecord = $cuUser->updateUserInformation( $request, $userLogin, $customerId, $encryptPassword, $flagUpdatePassword = true, $recordCuUser['user_id']  );
                /** Create or update a new user password on the google identity platform*/
                $recordGcp = $this->createOrUpdateAccountOnGcpServer( $loginId, $originalPassword );
            } else if (!$recordCuUser){
                /** create new user */
                $cuUserRecord = $cuUser->createUser($request, $customerId, $userLogin, $encryptPassword);
                /** Create or update a new user password on the google identity platform. */
                $recordGcp = $this->createOrUpdateAccountOnGcpServer( $loginId, $originalPassword );
            }
            if (!$recordGcp || !$cuUserRecord) {
                Log::error('Password creation or update failed. $recordGcp='. json_encode($recordGcp));
                return false;
            }
            $cuCustomerBranchCreate = $request->customer_branch_id;

            if ($cuUserRecord) {
                $userId = $cuUserRecord->user_id;
                /** Create / update customer representative and customer branch information */
                self::createUserUpdateDataCuCustomerUser($request, array_unique($cuCustomerBranchCreate), $userLogin);
                self::createUserUpdateOrCreateDataCuUserBranch($request, array_unique($cuCustomerBranchCreate), $customerId, $userId, $userLogin);
                Log::debug('Password creation or update scucessed');

                /** Create / update user's token */
                Log::info('Create a new one-time token');
                $token = Hash('sha256', ($userId . Carbon::now()));
                $newToken = self::createOrUpdateToken($userId, $token);
                $pageUrl = env('DOMAIN_FRONTEND'). CodeDefinition::ACTIVE_USER_EMAIL_PATH . '?token=' . $token;

                /** Get the user name and branch information for sending emails. */
                $cuCustomer = new CuCustomerDao();
                $cuCustomer = $cuCustomer->getCustomerName($customerId);
                $customerName =  $cuCustomer->customer_name ?: '';

                $cuCustomerBranch = new CuCustomerBranchDao();
                $cuCustomerBranchName = $cuCustomerBranch->getCustomerBranchName($cuCustomerBranchCreate);
                $cuCustomerBranchName =  $cuCustomerBranchName->customer_branch_name ?: '';
                /** Send a notification email to the customer.*/

                Log::debug("Send a notification email to the customer.");
                Mail::to($loginId)->send(
                    new ActivateUserEmail(
                        $customerName,
                        $cuCustomerBranchName,
                        $request->customer_user_name,
                        $loginId,
                        $pageUrl
                    )
                );
                return true;
            }
        } catch (\Exception $exception) {
            Log::error("An exception error has occurred. You can't send an email.");
            Log::error($exception);
            return false;
        }
    }

    /**
     * Create new user information from CSV.
     * @param $request
     * @param $loginUserId
     * @param $customerId
     * @param $customerBranchId
     * @return array|bool
     * @throws AuthException
     * @throws FirebaseException
     * @throws \Exception
     */
    public function createUserCSV( $request , $loginUserId, $customerId, $customerBranchId )
    {
        $loginId = $request['login_id'];
        $cuUser = new CuUserDao();

        $originalPassword = $this->registerNewPassword();
        $encryptPassword = $this->getEncryptPassword($originalPassword);
        if (!$encryptPassword) {
            Log::debug('Job CSV : Create new password failed');
            return false;
        }

        /** Register a new record in the cu_user table. */
        $createUser = $cuUser->createUserCSV($request, $customerId, $loginUserId, $encryptPassword);

        if ($createUser) {
            $userId = $createUser->user_id;

            /** Register an account on the platform google identity. */
            $recordGcp = $this->createOrUpdateAccountOnGcpServer( $loginId, $originalPassword );
            if (!$recordGcp) {
                Log::error('Failed to register a new account or update the user password by login_id ='. $this->removeEmailDomain($loginId));
                return false;
            }
            /** Newly register or renew a customer branch. */
            $flagUpdateBranchCsv = $this->updateDataImportCsv($request, $customerId, $customerBranchId, $loginUserId, $userId);
            if (is_array($flagUpdateBranchCsv)) {
                Log::error('Data update failed for users who have the email address of:'. $this->removeEmailDomain($loginId));
                return $flagUpdateBranchCsv;
            }
            try {
                /** Register or renew your account verification code. */
                Log::info('Create a new one-time token');
                $token = Hash('sha256', ($userId . Carbon::now()));
                $this->createOrUpdateToken($userId, $token);
            } catch (\Exception $exception) {
                /** $message_error_id = E400034 */
                $errorReturn['error'] = self::VALUE_ERROR_IMPORT_DEFAULT;
                $errorReturn['message_error'] = "ワンタイムトークン処理に失敗しました。システム管理者にお問い合わせください。";
                Log::error('Job import CSV : '.  $exception);
                return $errorReturn;
            }
            $pageUrl = env('DOMAIN_FRONTEND'). CodeDefinition::ACTIVE_USER_EMAIL_PATH . '?token=' . $token;

            /** Get the customer name BY the customer ID. */
            $cuCustomer = new CuCustomerDao();
            $cuCustomer = $cuCustomer->getCustomerName($customerId);
            $customerName =  $cuCustomer->customer_name ?: '';

            /** Get the customer branch name by branch ID.*/
            $cuCustomerBranch = new CuCustomerBranchDao();
            $cuCustomerBranchName = $cuCustomerBranch->getCustomerBranchName([$customerBranchId]);
            $cuCustomerBranchName =  $cuCustomerBranchName->customer_branch_name ?: '';

            try {
                Mail::to($loginId)->send(
                    new ActivateUserEmail(
                        $customerName,
                        $cuCustomerBranchName,
                        $request['customer_user_name'],
                        $request['login_id'],
                        $pageUrl
                    )
                );
                return true;
            } catch (\Exception $exception) {
                Log::error( $exception->getMessage() . '----Line----' . $exception->getLine());
                return $userId;
            }
        }
        Log::error('Failed to import the user in the CSV file. $createUser='. json_encode($createUser, JSON_UNESCAPED_UNICODE));
        return false;
    }

    /**
     * Updated user information.
     * @param $request
     * @return array|bool
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateUser( $request )
    {
        try {
            $userId = Auth::user()->user_id;
            $customerId = Auth::user()->customer_id;
            $flagUpdatePassword = false;
            $encryptPassword = '';
            $cuUser = new CuUserDao();
            /**  Get user information and check the access authority of the logged-in user. */
            $userRecord = $cuUser->getUserByUserIdWithCheckPermission($request->user_id);
            if (!$userRecord) {
                return [
                    'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }
            $originalPassword = $this->registerNewPassword();
            if ($request->customer_user_email != $userRecord->login_id) {
                $flagUpdatePassword = true;
                $encryptPassword = $this->getEncryptPassword($originalPassword);
                if (!$encryptPassword) {
                    Log::debug('Create new password failed');
                    return false;
                }
            }
            /** Is the user a system administrator? */
            $userRole = $cuUser->getRoleSystemOwner($request->user_id);
            if ( $userRole ) {
                if ( $request->role_id != CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR ) {
                    Log::error('Update user information failed, update role_id is different from role value of system admin');
                    return [
                        'statusCode' => Response::HTTP_BAD_REQUEST,
                        'errorMess' => '400_invalid_role_system_owner'
                    ];
                }
                if ( $request->user_lock ) {
                    Log::error('Update user information failed, update user_lock value is true');
                    return [
                        'statusCode' => Response::HTTP_BAD_REQUEST,
                        'errorMess' => '400_invalid_user_lock_system_owner'
                    ];
                }
            }

            /** Updated user information. */
            $cuUserRecord = $cuUser->updateUserInformation($request, $userId, $customerId, $encryptPassword, $flagUpdatePassword );
            if (!$cuUserRecord) {
                Log::error('Not exists account for update user. $userId='. $userId .'$cuUserRecord = '. $cuUserRecord);
                return false;
            }

            if ($flagUpdatePassword) {
                /**Update user password to google identity platform. */
                $this->createOrUpdateAccountOnGcpServer( $request->customer_user_email, $originalPassword, $flagUpdate = true, $userRecord->login_id );
            }

            /** Updated customer contact information. */
            $arrCuUserBranchUpdate = $request->customer_branch_id;
            if ( is_array($arrCuUserBranchUpdate) && count($arrCuUserBranchUpdate) > 0) {

                $this->updateDataCuCustomerUser($request, array_unique($arrCuUserBranchUpdate), $userId);

                Log::debug('The customer contact information has been updated successfully.');

                $this->updateDataCuUserBranch($request, array_unique($arrCuUserBranchUpdate), $customerId, $request->get('user_id'), $userId);
                Log::debug('The customer branch information has been updated successfully.');
            } else {
                Log::error('Failed to update customer branch and customer contact information. $arrCuUserBranchUpdate='. json_encode($arrCuUserBranchUpdate) );
                return false;
            }
            if ($flagUpdatePassword) {
                /** Create / update user's Token */
                Log::info('Create a new one-time token');
                $token = Hash('sha256', ($userRecord->user_id . Carbon::now()));
                $this->createOrUpdateToken($userRecord->user_id, $token);
                $pageUrl = env('DOMAIN_FRONTEND'). CodeDefinition::ACTIVE_USER_EMAIL_PATH . '?token=' . $token;

                /** Get the customer name by the customer ID. */
                $cuCustomer = new CuCustomerDao();
                $cuCustomer = $cuCustomer->getCustomerName($userRecord->customer_id);
                $customerName = $cuCustomer->customer_name ?: '';

                /** Get the customer branch name by branch ID. */
                $cuCustomerBranch = new CuCustomerBranchDao();
                $cuCustomerBranchName = $cuCustomerBranch->getCustomerBranchName($arrCuUserBranchUpdate);
                $cuCustomerBranchName = $cuCustomerBranchName->customer_branch_name ?: '';

                /** If the password is updated, the user's email will also be sent as a notification email. */
                Log::debug('Send a notification email to the customer.$request->customer_user_email='. $this->removeEmailDomain($request->customer_user_email));
                Mail::to($request->customer_user_email)->send(
                    new UpdatePasswordEmail(
                        $customerName,
                        $cuCustomerBranchName,
                        $request->customer_user_name,
                        $request->customer_user_email,
                        $pageUrl
                    )
                );
            }
            return true;
        } catch (\Exception $exception) {
            Log::error( $exception->getMessage() . '----Line----' . $exception->getLine());
            return false;
        }
    }

    /**
     * Get user information by email.
     * @param $loginId
     * @return mixed
     */
    public function getUserByEmailAddress ( $loginId ) {
        $cuUser = new CuUserDao();
        return $cuUser->getUserByMaillAddress($loginId);
    }

    /**
     * Update customer information from CSV.
     * @param $request
     * @param $userLoginId
     * @param $customerId
     * @param $customerBranchId
     * @param $recordCuUser
     * @return mixed
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateUserDataCsv ($request, $userLoginId, $customerId, $customerBranchId, $recordCuUser)
    {
        $loginId = $recordCuUser['login_id'];
        $cuUser = new CuUserDao();
        $flagUpdatePassword = false;

        /** Check the access_flag of your account.s  */
        if ($recordCuUser['access_flg'] == DaoConstants::CU_USER_ACCESS_FLAG) {
            $flagUpdatePassword = true;
            /** General new password */
            $originalPassword = $this->registerNewPassword();
            $encryptPassword = $this->getEncryptPassword($originalPassword);
            if (!$encryptPassword) {
                Log::debug('Job CSV : Create new password failed');
                return false;
            }
            /** Update user information and set default password. */
            $cuUserUpdate = $cuUser->updateUserInformationCSV( $request, $customerId, $userLoginId, $flagUpdatePassword, $encryptPassword );
            /** Set a default password for the user's account on the google identity platform. */
            $recordGcp = $this->createOrUpdateAccountOnGcpServer( $loginId, $originalPassword );
            if (!$recordGcp) {
                Log::debug('Failed to update user information on google identity platform. $recordGcp='. $recordGcp);
                return false;
            }
        } else { /** Update user information without setting a default password. */
            $cuUserUpdate = $cuUser->updateUserInformationCSV($request, $customerId, $userLoginId, $flagUpdatePassword );
        }

        Log::debug('The account information was successfully updated in the cu_user table.');
        if ($cuUserUpdate) {
            $userId = $cuUserUpdate->user_id;
            /** Updated user branch information. */
            $flagUpdateBranchCsv = $this->updateDataImportCsv($request, $customerId, $customerBranchId, $userLoginId, $userId);
            if (is_array($flagUpdateBranchCsv)) {
                Log::error("Failed to update the user's customer branch information.");
                return $flagUpdateBranchCsv;
            }
        }
        if ($flagUpdatePassword) {
            try {
                /** Create a new user's one-time password. */
                $token = Hash('sha256', ($cuUserUpdate->user_id . Carbon::now()));
                $this->createOrUpdateToken($cuUserUpdate->user_id, $token);
            } catch (\Exception $exception) {
                /** $message_error_id = E400034 */
                $errorReturn['error'] = self::VALUE_ERROR_IMPORT_DEFAULT;
                $errorReturn['message_error'] = "ワンタイムトークン処理に失敗しました。システム管理者にお問い合わせください。";
                Log::error('Job import CSV : '.  $exception);
                return $errorReturn;
            }
            $pageUrl = env('DOMAIN_FRONTEND'). CodeDefinition::RESET_PASSWORD_USER_EMAIL_PATH . '?token=' . $token;
            /** replace character in email @var $loginId */
            $loginIdUserWriteLog = substr_replace($cuUserUpdate->login_id, '*****', 1, strpos($cuUserUpdate->login_id, '@') - 2);

            /** Get the customer name by the customer ID. */
            $cuCustomer = new CuCustomerDao();
            $cuCustomer = $cuCustomer->getCustomerName($cuUserUpdate->customer_id);
            $customerName =  $cuCustomer->customer_name ?: '';

            /** Get the customer branch name by branch ID.*/
            $cuCustomerBranch = new CuCustomerBranchDao();
            $cuCustomerBranchName = $cuCustomerBranch->getCustomerBranchName([$customerBranchId]);
            $cuCustomerBranchName =  $cuCustomerBranchName->customer_branch_name ?: '';

            Log::debug('Send a notification email to the customer.$cuUserUpdate->login_id=' . $this->removeEmailDomain($cuUserUpdate->login_id));
            Mail::to($cuUserUpdate->login_id)->send(
                new UpdateUserImportNotLogin(
                    $customerName,
                    $cuCustomerBranchName,
                    $cuUserUpdate->customer_user_name,
                    $cuUserUpdate->login_id,
                    $pageUrl
                )
            );
        }
        Log::debug('Updated user information successfully');
        return true;
    }

    /**
     * Get user information by email.
     * @param $mailAddress
     * @return mixed
     */
    public function checkCuUserByMaillAddress($mailAddress)
    {
        $cuUser = new CuUserDao();
        return $cuUser->getInfoByMaillAddress($mailAddress);
    }

    /** Set sorting conditions.
     * @param $orderBy
     * @return array
     */
    protected function convertSortConditionToColumnQueryUserList( $orderBy )
    {
        $sorts = [];
        if (!empty($orderBy))
        {
            switch ($orderBy) {
                case 'branch_name_asc':
                    $sorts['column'] = 'cu_customer_branch.customer_branch_name';
                    $sorts['order'] = 'asc';
                    break;
                case 'branch_name_desc':
                    $sorts['column'] = 'cu_customer_branch.customer_branch_name';
                    $sorts['order'] = 'desc';
                    break;
                case 'user_name_asc':
                    $sorts['column'] = 'cu_user.customer_user_name';
                    $sorts['order'] = 'asc';
                    break;
                case 'user_name_desc':
                    $sorts['column'] = 'cu_user.customer_user_name';
                    $sorts['order'] = 'desc';
                    break;
                case 'permission_asc':
                    $sorts['column'] = 'system_use_permission';
                    $sorts['order'] = 'asc';
                    break;
                case 'permission_desc':
                    $sorts['column'] = 'system_use_permission';
                    $sorts['order'] = 'desc';
                    break;
                case 'role_name_asc':
                    $sorts['column'] = 'cu_role.role_name';
                    $sorts['order'] = 'asc';
                    break;
                case 'role_name_desc':
                    $sorts['column'] = 'cu_role.role_name';
                    $sorts['order'] = 'desc';
                    break;
                case 'login_id_desc':
                    $sorts['column'] = 'cu_user.login_id';
                    $sorts['order'] = 'desc';
                    break;
                case 'login_id_asc':
                    $sorts['column'] = 'cu_user.login_id';
                    $sorts['order'] = 'asc';
                    break;
                default:
                    break;
            }
        }
        return $sorts;
    }
    /**
     * Updated user contact information.
     * @param $request
     * @param $arrCuUserBranchCreate
     * @param $userLogin
     * @return bool
     */
    protected function createUserUpdateDataCuCustomerUser($request, $arrCuUserBranchCreate, $userLogin)
    {
        $cuCustomerUser = new CuCustomerUserDao();
        if (count($arrCuUserBranchCreate) > 0){
            foreach ($arrCuUserBranchCreate as $customerBranchId) {
                // Get customer user information
                $recordCuCustomerUserIds = $cuCustomerUser->getCuCustomerUser($request->customer_user_email, $customerBranchId);
                if ($recordCuCustomerUserIds) {
                    $recordCuCustomerUserIds->customer_user_name        = $request->get('customer_user_name');
                    $recordCuCustomerUserIds->customer_user_name_kana   = $request->get('customer_user_name_kana');
                    $recordCuCustomerUserIds->customer_user_email       = $request->get('customer_user_email');
                    $recordCuCustomerUserIds->customer_user_tel         = $request->get('customer_user_tel');
                    $recordCuCustomerUserIds->customer_reminder_sms_flag = $request->get('customer_reminder_sms_flag');
                    $recordCuCustomerUserIds->update_user_id            = $userLogin;
                    $recordCuCustomerUserIds->save();

                }
            }
            Log::debug('The customer contact information has been updated successfully.');
            return true;
        }
        Log::error('Failed to update customer contact information. count($arrCuUserBranchCreate)='. count($arrCuUserBranchCreate));
        return false;
    }

    /**
     * Create / update user customer branch information.
     * @param $request
     * @param $arrCuUserBranchIds
     * @param $customerId
     * @param $userId
     * @param $userLogin
     * @return CuUserBranch|bool
     */
    protected function createUserUpdateOrCreateDataCuUserBranch($request, $arrCuUserBranchIds, $customerId, $userId, $userLogin)
    {
        $dataUserBranchCreate = [];
        $cuUserBranch = new CuUserBranchDao();
        if (count($arrCuUserBranchIds) > 0 ) {
            foreach ($arrCuUserBranchIds as $customerBranchId) {
                /** Get customer branch information. */
                $recordCuUserBranchIds = $cuUserBranch->getCuUserBranchId($userId, $customerBranchId);
                if (!$recordCuUserBranchIds) { /** Create new customer branch information for the user. */
                    $dataUserBranchCreate[] = [
                        "user_id"                   => $userId,
                        "belong"                    => $request->get('belong'),
                        "customer_id"               => $customerId,
                        "customer_branch_id"        => $customerBranchId,
                        "create_user_id"            => $userLogin,
                        "create_system_type"        => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "update_user_id"            => $userLogin,
                        "update_system_type"        => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "status"                    => DaoConstants::STATUS_ACTIVE
                    ];
                } else { /** Updated user customer branch information. */
                    $recordCuUserBranchIds->belong = $request->get('belong');
                    $recordCuUserBranchIds->update_user_id = $userLogin;
                    $recordCuUserBranchIds->save();
                }
            }
            /** Create new customer branch information for the user. */
            if (count($dataUserBranchCreate) > 0) {
                Log::debug('create record in cu_user_branch sucessfully');
                return $cuUserBranch->createCuUserBranch($dataUserBranchCreate);
            }
            Log::error('create record in cu_user_branch fail. count($dataUserBranchCreate)='. count($dataUserBranchCreate));
            return false;
        }
    }

    /**
     * Update / delete customer_user information.
     * @param $request
     * @param $arrCuUserBranchUpdate
     * @param $userLogin
     * @return bool|false
     */
    protected function updateDataCuCustomerUser($request, $arrCuUserBranchUpdate, $userLogin)
    {
        $arrDataCustomerUserDelete = [];
        $cuCustomerUser = new CuCustomerUserDao();
        /** Get the list of customer_user to delete. */
        $dataCustomerUserDelete = $cuCustomerUser->getListCuCustomerUserDelete($request->customer_user_email, $arrCuUserBranchUpdate);

        if (count($dataCustomerUserDelete) > 0) {
            foreach ($dataCustomerUserDelete as $itemDelete) {
                $arrDataCustomerUserDelete[] = $itemDelete['customer_branch_id'];
            }
            /**Deleted customer_user information. */
            $cuCustomerUser->deleteRecordCustomerUser($request->customer_user_email, $arrDataCustomerUserDelete);
        }

        if (count($arrCuUserBranchUpdate) > 0) {
            foreach ($arrCuUserBranchUpdate as $customerBranchId) {
                $recordCuCustomerUSerUpdate = $cuCustomerUser->getCustomerUserDetail($request->customer_user_email, $customerBranchId);
                if (!$recordCuCustomerUSerUpdate) {
                    Log::error('create record in u_customer_user sucessfully. $recordCuCustomerUSerUpdate='. $recordCuCustomerUSerUpdate);
                    return false;
                }
                /** Updated customer_user information. */
                $recordCuCustomerUSerUpdate->customer_user_name         = $request->get('customer_user_name');
                $recordCuCustomerUSerUpdate->customer_user_name_kana    = $request->get('customer_user_name_kana');
                $recordCuCustomerUSerUpdate->customer_user_email        = $request->get('customer_user_email');
                $recordCuCustomerUSerUpdate->customer_user_tel          = $request->get('customer_user_tel');
                $recordCuCustomerUSerUpdate->customer_reminder_sms_flag = $request->get('customer_reminder_sms_flag');
                $recordCuCustomerUSerUpdate->update_user_id             = $userLogin;
                $recordCuCustomerUSerUpdate->update_system_type         = DaoConstants::CUSTOMER_SYSTEM_TYPE;
                $recordCuCustomerUSerUpdate->status         = DaoConstants::STATUS_ACTIVE;
                $recordCuCustomerUSerUpdate->save();
            }
        }
        Log::debug('create record in u_customer_user fail. count($arrCuUserBranchUpdate)='. count($arrCuUserBranchUpdate));
        return true;
    }

    /**
     * Create / update / delete customer branch information.
     * @param $request
     * @param $arrCuUserBranchUpdate
     * @param $customerId
     * @param $userId
     * @param $userLogin
     * @return CuUserBranch
     */
    protected function updateDataCuUserBranch($request, $arrCuUserBranchUpdate, $customerId, $userId, $userLogin)
    {
        $dataUserBranchCreate = [];
        $dataUserBranchDelete = [];
        $cuUserBranch = new CuUserBranchDao();
        /** Get the list of customer branch information to be deleted.*/
        $recordCuUserBranchIds = $cuUserBranch->getCuUserBranchIdDelete($userId, $arrCuUserBranchUpdate);

        /** Deleted customer branch information.*/
        if (count($recordCuUserBranchIds) > 0) {
            foreach ($recordCuUserBranchIds as $itemDelete) {
                $dataUserBranchDelete[] = $itemDelete['user_branch_id'];
            }
            $cuUserBranch->deleteRecordUserBranch($dataUserBranchDelete);
        }

        /** Foreach get data create , data update */
        if (count($arrCuUserBranchUpdate) > 0) {
            foreach ($arrCuUserBranchUpdate as $customerBranchId) {
                $recordCuUserBranch = $cuUserBranch->getCuUserBranchId($userId, $customerBranchId);
                if (!$recordCuUserBranch) { /** Create new customer branch information. */
                    $dataUserBranchCreate[] = [
                        "user_id"                   => $userId,
                        "belong"                    => $request->get('belong'),
                        "customer_id"               => $customerId,
                        "customer_branch_id"        => $customerBranchId,
                        "create_user_id"            => $userLogin,
                        "create_system_type"        => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "update_user_id"            => $userLogin,
                        "update_system_type"        => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "status"                    => DaoConstants::STATUS_ACTIVE
                    ];
                } else { /** Updated customer branch information.*/
                    Log::debug('Updated customer branch information.$recordCuUserBranch='. $recordCuUserBranch);
                    $recordCuUserBranch->belong = $request->get('belong');
                    $recordCuUserBranch->update_user_id = $userLogin;
                    $recordCuUserBranch->save();
                }
            }
        }
        /** Create new customer branch information.  */
        Log::debug('Create new customer branch information. ');
        return $cuUserBranch->createCuUserBranch($dataUserBranchCreate);
    }

    /**
     * Update customer information from CSV.
     * @param $request
     * @param $customerId
     * @param $customerBranchId
     * @param $userLogin
     * @param $userId
     * @return array|bool
     */
    protected function updateDataImportCsv($request, $customerId, $customerBranchId, $userLogin, $userId)
    {
        $errorReturn['error'] = self::VALUE_ERROR_IMPORT_DEFAULT;
        $belong = DaoConstants::STATUS_INACTIVE;
        if ($request['belong'] == self::VALUE_CSV_BELONG_ACTIVE){
            $belong = DaoConstants::STATUS_ACTIVE;
        }
        $cuUserBranch = new CuUserBranchDao();
        // Get customer branch information.
        $dataUserBranch = $cuUserBranch->getUserBranch($userId, $customerBranchId);

        if ($dataUserBranch) {
            if ($request['process_type'] == self::VALUE_CSV_PROCESS_TYPE_DELETE) {
                try {
                    /** Deleted customer branch information. */
                    $dataUserBranch->delete();
                    Log::debug('The customer branch information was successfully deleted. $dataUserBranch='. json_encode($dataUserBranch, JSON_UNESCAPED_UNICODE));
                } catch (\Exception $exception) {
                    /** $message_error_id = E400034 */
                    $errorReturn['message_error'] = "顧客支店所属情報削除に失敗しました。システム管理者にお問い合わせください。";
                    Log::error('Job import CSV : '.  $exception);
                    return $errorReturn;
                }
            } else {
                try {
                    /** Updated customer branch information. */
                    $dataUserBranch->belong = $belong;
                    $dataUserBranch->update_user_id     = $userLogin;
                    $dataUserBranch->save();
                    Log::debug('The customer branch information has been updated successfully. $dataUserBranch='. json_encode($dataUserBranch, JSON_UNESCAPED_UNICODE));
                } catch (\Exception $exception) {
                    /** $message_error_id = E400034 */
                    $errorReturn['message_error'] = "顧客支店所属情報登録に失敗しました。システム管理者にお問い合わせください。";
                    Log::error('Job import CSV : '.  $exception);
                    return $errorReturn;
                }

            }
        } else {
            if ($request['process_type'] == self::VALUE_CSV_PROCESS_TYPE_ADD) {
                try {
                    /** Create new customer branch information.*/
                    $dataUserBranchCreate = [
                        "user_id" => $userId,
                        "belong" => $belong,
                        "customer_id" => $customerId,
                        "customer_branch_id" => $customerBranchId,
                        "create_user_id" => $userLogin,
                        "create_system_type" => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "update_user_id" => $userLogin,
                        "update_system_type" => DaoConstants::CUSTOMER_SYSTEM_TYPE,
                        "status" => DaoConstants::STATUS_ACTIVE
                    ];
                    $recordUserBranchCreate = $cuUserBranch->createCuUserBranch($dataUserBranchCreate);
                    if (!$recordUserBranchCreate){
                        Log::error('Failed to create new customer branch information.$recordUserBranchCreate='. json_encode($recordUserBranchCreate, JSON_UNESCAPED_UNICODE));
                        return false;
                    }
                } catch (\Exception $exception) {
                    /** $message_error_id = E400034 */
                    $errorReturn['message_error'] = "顧客支店所属情報登録に失敗しました。システム管理者にお問い合わせください。";
                    Log::error('Job import CSV : '.  $exception);
                    return $errorReturn;
                }

            }
        }

        /** Updated customer_user information. */
        try {
            $cuCustomerUser = new CuCustomerUserDao();
            $dataCustomerUser = $cuCustomerUser->getCustomerUser($customerBranchId, $request['login_id']);
            if ($dataCustomerUser) {
                if ($request['process_type'] == self::VALUE_CSV_PROCESS_TYPE_DELETE) {
                    $dataCustomerUser->status = DaoConstants::STATUS_INACTIVE;
                } else {
                    $dataCustomerUser->status = DaoConstants::STATUS_ACTIVE;
                }
                $dataCustomerUser->customer_user_name = $request['customer_user_name'];
                $dataCustomerUser->customer_user_name_kana = $request['customer_user_name_kana'];
                $dataCustomerUser->customer_user_email = $request['login_id'];
                $dataCustomerUser->customer_user_tel = $request['customer_user_tel'];
                $dataCustomerUser->update_user_id = $userLogin;
                $dataCustomerUser->save();
                Log::debug('The customer contact information has been updated successfully. $dataCustomerUser='. json_encode($dataCustomerUser));
            }
        } catch (\Exception $exception) {
            /** $message_error_id = E400034 */
            $errorReturn['message_error'] = "顧客担当者情報登録に失敗しました。システム管理者にお問い合わせください。";
            Log::error('Job import CSV : '.  $exception);
            return $errorReturn;
        }

        Log::debug('You have successfully updated customer information from CSV.');
        return true;
    }

    /**
     * Create / update user information on google identity platform.
     * @param $loginId
     * @param $originalPassword
     * @param null $flagUpdate
     * @param null $loginIdDelete
     * @return bool
     * @throws AuthException
     * @throws FirebaseException
     */
    protected function createOrUpdateAccountOnGcpServer ( $loginId, $originalPassword, $flagUpdate = null, $loginIdDelete = null )
    {
        $userRecordGcs = false;
        /** check the existence of the account on the google identity platform. */
        try {
            $userRecordGcs = $this->fireBaseAuth->getUserByEmail($loginId);
        } catch (\Exception $e){
            Log::debug('The account does not exist on the google identity platform.');
            Log::error($e);
        }
        /** Create / update user information on google identity platform。 */
        if (!$userRecordGcs) {
            if ($flagUpdate && !empty($loginIdDelete)) {
                $userRecordGcsDelete = false;
                try {
                    $userRecordGcsDelete = $this->fireBaseAuth->getUserByEmail($loginIdDelete);
                } catch (\Exception $e){
                    Log::error($e);
                }
                if ($userRecordGcsDelete) {
                    /** Delete old account information on google identity platform. */
                    try {
                        $this->fireBaseAuth->deleteUser($userRecordGcsDelete->uid);
                        Log::debug('Successful deletion of default account on google identity platform');
                    } catch (\Exception $e){
                        Log::error('Failed to delete old account information on google identity platform.');
                        Log::error($e);
                        return false;
                    }
                }
            }
            // Create new account information on google identity platform.
            try {
                $this->fireBaseAuth->createUser([
                    'email' => $loginId,
                    'password' => $originalPassword
                ]);
                Log::debug('Successful account creation on google identity platform');
                return true;
            } catch (\Exception $e){
                Log::error('Failed to create new account information on google identity platform');
                Log::error($e);
                return false;
            }
        } else {
            /**Update password of account on the google identity platform. */
            try {
                $this->fireBaseAuth->changeUserPassword($userRecordGcs->uid, $originalPassword);
                Log::debug('Successful reset of default account password on google identity platform');
                return true;
            } catch (\Exception $e){
                Log::debug('Failed to delete the account information on the google identity platform.');
                Log::error($e);
                return false;
            }
        }
    }

    /**
     * Newly created / updated token information
     * @param $userId
     * @param $token
     * @return \App\Models\CuToken|mixed
     */
    protected function createOrUpdateToken($userId, $token)
    {
        $cuToken = new CuTokenDao();
        $tokenRecord = $cuToken->getTokenByUserId($userId);
        if ($tokenRecord) {
            $cuToken->deleteTokenByUserId($userId);
        }
        return $cuToken->createNewToken($userId, $token);
    }

    /**
     * Get user information by email.
     * @param $loginId
     * @return mixed
     */
    protected function getUserCsvByEmailAddress ( $loginId ) {
        $cuUser = new CuUserDao();
        return $cuUser->getUserImportCsvByMaillAddress($loginId);
    }


    /**
     * General encrypt password
     * @return false|string|void
     * @throws \Exception
     */
    public function getEncryptPassword ($originalPassword) {
        return $this->commonHelper->encryptData($originalPassword);
    }

    /**
     * register new Password
     */
    public function registerNewPassword() {
        $passwordLength = CodeDefinition::PASSWORD_LENGTH;

        return $this->commonHelper->generateString($passwordLength);
    }
}
