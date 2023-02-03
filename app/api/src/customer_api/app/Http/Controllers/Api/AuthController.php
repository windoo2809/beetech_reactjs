<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\AuthLockRequest;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\Auth\AuthWithBranchRequest;
use App\Services\CuUserService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Common\CustomResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;
    protected $customResponse;
    protected $cuUserService;

    /**
     * AuthController constructor.
     * @param CustomResponse $customResponse
     * @param AuthService $authService
     * @param CuUserService $cuUserService
     */
    public function __construct(CustomResponse $customResponse, AuthService $authService, CuUserService $cuUserService)
    {
        parent::__construct();
        $this->authService = $authService;
        $this->customResponse = $customResponse;
        $this->cuUserService = $cuUserService;
    }

    /**
     * Login default
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function authenticate(AuthRequest $request)
    {
        try {
            $result = $this->authService->checkAuth();
        } catch (\Exception $e) {
            Log::error($e);
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR, "");
            return $this->customResponse->sendResponse($response);
        }
        if (!$result) {
            $message = trans('attributes.401_unauthorized');
            Log::error("Unauthorized. \$result=". $result);
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_UNAUTHORIZED);
            return $this->customResponse->sendResponse($response);
        }
        /** Check exist token when first login */
        !empty($result['token']) ? $responseToken = $result['token'] : $responseToken = null;

        $message = trans('attributes.success');
        $response = $this->customResponse->getSuccessResponse(
            [],
            CustomResponse::RESPONSE_TOTAL_HIDE,
            $message,
            $result['page'],
            Response::HTTP_OK,
            $result['access_token'],
            $responseToken
        );
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Login with branchID
     * @param AuthWithBranchRequest $request
     * @return mixed
     */
    public function authenWithBranch(AuthWithBranchRequest $request)
    {
        try {
            $result = $this->authService->checkAuthWithBranch(Auth::user()->uid, $request->customer_id, $request->customer_branch_id);
        } catch (\Exception $e) {
            Log::error($e);
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR, "");
            return $this->customResponse->sendResponse($response);
        }
        /**
         * undocumented constant
         **/
        if (!$result) {
            $message = trans("attributes.401_unauthorized");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_UNAUTHORIZED);
            return $this->customResponse->sendResponse($response);
        } else if( is_array($result) && $result['statusCode'] == Response::HTTP_FORBIDDEN) {
            $message = trans("attributes.403_forbidden");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
            return $this->customResponse->sendResponse($response);
        }

        $message = trans('attributes.success');
        $response = $this->customResponse->getSuccessResponse(
            [],
            CustomResponse::RESPONSE_TOTAL_HIDE,
            $message,
            null,
            Response::HTTP_OK,
            $result['access_token']
        );
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Update status user lock
     * @param AuthLockRequest $request
     * @return false|mixed
     */
    public function updateLock(AuthLockRequest $request)
    {
        try {
            $isLocked = $this->authService->authLock($request->login_id);
            Log::debug('The user is locked.');
        } catch (\Exception $e) {
            Log::error($e);
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR, "");
            return $this->customResponse->sendResponse($response);
        }

        if (!$isLocked) {
            $message = trans("attributes.403_forbidden");
            Log::error("You don't have permission to lock user. \$isLocked=". $isLocked);
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
            return $this->customResponse->sendResponse($response);
        }

        $message = trans('attributes.success');
        $response = $this->customResponse->getSuccessResponse(
            [],
            $total = CustomResponse::RESPONSE_TOTAL_HIDE,
            $message,
            null,
            $statusCode = Response::HTTP_OK,
            null
        );
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try {
            $this->authService->logout();
        } catch (\Exception $e) {
            Log::error($e);
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR, "");
            return $this->customResponse->sendResponse($response);
        }

        $message = trans('attributes.success');
        $response = $this->customResponse->getSuccessResponse(
            null,
            CustomResponse::RESPONSE_TOTAL_HIDE,
            $message,
        );
        return $this->customResponse->sendResponse($response);
    }
}
