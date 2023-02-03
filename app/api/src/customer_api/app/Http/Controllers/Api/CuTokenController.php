<?php

namespace App\Http\Controllers\Api;

use App\Common\CustomResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CuToken\CheckTokenRequest;
use App\Http\Requests\CuToken\CuTokenRequest;
use App\Services\AuthService;
use App\Services\CuUserService;
use App\Services\CuUserTokenService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CuTokenController extends Controller
{
    /**
     * @var $cuTokenService
     */
    protected $cuTokenService;
    /**
     * @var customResponse
     */
    protected $customResponse;
    /**
     * @var CuUserService
     */
    protected $cuUserService;
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * CuMessageController constructor.
     *
     * @param CustomResponse $customResponse
     * @param CuUserTokenService $cuTokenService
     * @param CuUserService $cuUserService
     * @param AuthService $authService
     */
    public function __construct(CustomResponse $customResponse, CuUserTokenService $cuTokenService, CuUserService $cuUserService, AuthService $authService)
    {
        $this->customResponse = $customResponse;
        $this->cuTokenService = $cuTokenService;
        $this->cuUserService = $cuUserService;
        $this->authService = $authService;
    }

    /**
     * Reset password
     *
     * @param CuTokenRequest $request
     * @return JsonResponse
     */
    public function sendOnetimeToken(CuTokenRequest $request)
    {
        try {
            $message = trans('attributes.success');
            $response = $this->customResponse->getSuccessResponse([], CustomResponse::RESPONSE_TOTAL_HIDE, $message);

            Log::debug('Validate your email address');
            $cuUser = $this->cuUserService->checkCuUserByMaillAddress($request->mail_address);
            if (!$cuUser) {
                Log::error('The email address does not exist on the system. \$cuUser='. json_encode($cuUser, JSON_UNESCAPED_UNICODE));
                return $this->customResponse->sendResponse($response);
            }

            Log::debug('Create a new one-time token and send an email to your customers.');
            $result = $this->cuTokenService->createToken($cuUser->user_id, $cuUser->customer_user_name, $request->mail_address);

        } catch (\Exception $e) {
            Log::error($e);
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Check the one-time Token 
     * @param CheckTokenRequest $request
     * @return JsonResponse
     */
    public function checkToken ( CheckTokenRequest $request )
    {
        DB::beginTransaction();
        try {
            $token = $request->token;
            $dataReturn = [];
            $token_status = FALSE;
            $access_token = FALSE;
            $result = $this->cuTokenService->verifyToken($token);
            if ($result) {
                $token_status = TRUE;
                /** Enable user.*/
                if ($request->user_active) {
                    $userId = $result->user_id;
                    $access_token = $this->cuTokenService->activeUser($request, $userId);
                }
            }
            DB::commit();

            $dataReturn['token_status'] = $token_status;
            $response = $this->customResponse->getSuccessResponse(
                $dataReturn,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                $message = trans('attributes.success'),
                null,
                Response::HTTP_OK,
                $access_token
            );
            Log::debug("Verify one-time token successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }
}
