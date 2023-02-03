<?php


namespace App\Services;

use App\Common\CodeDefinition;
use App\Dao\DaoConstants;
use App\Dao\MultiTable\CuUserMultiDao;
use App\Dao\SingleTable\CuCustomerUserDao;
use App\Dao\SingleTable\CuUserBranchDao;
use App\Dao\SingleTable\CuUserDao;
use App\Dao\SingleTable\CuTokenDao;
use App\Mail\ResetPassword;
use App\Models\CuUserBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Common\CommonHelper;

class CuUserTokenService
{
    protected $commonHelper;

    protected $authService;

    /**
     * CuUserTokenService constructor.
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService,CommonHelper $commonHelper)
    {
        $this->authService = $authService;
        $this->commonHelper = $commonHelper;
    }

    /**
     * Create a one-time token for the first login..
     * @param $userId
     * @param $userName
     * @param $mailAddress
     * @return boolean
     */
    public function createToken($userId, $userName, $mailAddress)
    {
        $onetimeToken = Hash('sha256', ($userId . Carbon::now()));
        $tokenExpirationDate = '';

        $cuTokenDao = new CuTokenDao();
        $cuUserMultiDao = new CuUserMultiDao();
        $existingTokenInfo = $cuUserMultiDao->getTokenByMaillAddress($mailAddress);
        if ($existingTokenInfo) {
            Log::info('Delete the existing one-time token.');
            $cuTokenDao->deleteToken($existingTokenInfo->cu_token);
        }
        Log::info('Create a new one-time token');

        $newTokenInfo = $cuTokenDao->createNewToken($userId, $onetimeToken);
        $tokenExpirationDate = $newTokenInfo->expire;

        $pageUrl = env('DOMAIN_FRONTEND'). CodeDefinition::RESET_PASSWORD_USER_EMAIL_PATH . '?token=' . $onetimeToken;

        /** Send an email with a one-time token and password reset URL. */
        Mail::to($mailAddress)->send(new ResetPassword($mailAddress, $userName, $pageUrl, $tokenExpirationDate));

        return true;
    }

    /**
     * Check the one-time token.
     * @param $token
     * @return mixed
     */
    public function verifyToken ( $token )
    {
        $cuToken = new CuTokenDao();
        return $cuToken->getInfoByToken( $token );
    }

    /**
     * Enable user.
     * @param $request
     * @param $userId
     * @return mixed
     */
    public function activeUser ( $request, $userId )
    {
        /**Enable user. */
        $cuToken = new CuUserDao();
        $userRecord = $cuToken->getUserByUserId( $userId );

        /** Enable the user's customer branch information. */
        if ($userRecord){
            $password = $this->commonHelper->decryptData($userRecord->password);
            $request->merge([
                'login_id' => $userRecord->login_id,
                'password' => $password
            ]);
            $accessToken =  $this->authService->generateAccessTokenLoginFirstTime();
            if ($accessToken) {
                return $accessToken['access_token'];
            }
        }
        return false;
    }
}
