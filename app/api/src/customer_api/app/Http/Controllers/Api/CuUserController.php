<?php

namespace App\Http\Controllers\Api;

use App\Common\CodeDefinition;
use App\Common\CustomResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CuUserService;
use App\Services\CuUserTokenService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Request;

class CuUserController extends Controller
{
    /**
     * @var customResponse
     */
    protected $customResponse;
    protected $cuUserService;
    protected $authService;
    protected $cuTokenService;

    /**
     * CuMessageController constructor.
     *
     * @param CustomResponse $customResponse
     * @param CuUserTokenService $cuTokenService
     */
    public function __construct(CustomResponse $customResponse, CuUserTokenService $cuTokenService, CuUserService $cuUserService, AuthService $authService)
    {
        $this->customResponse = $customResponse;
        $this->cuTokenService = $cuTokenService;
        $this->cuUserService = $cuUserService;
        $this->authService = $authService;
    }

    /**
     * get user list
     * @param Request $request
     * @return JsonResponse
     */
    public function index (Request $request) {
        try {
            $result = [];
            $customerID = Auth::user()->customer_id;
            $countTotal = $this->cuUserService->getCountUser($request, $customerID);
            if ($countTotal > 0) {
                $result = $this->cuUserService->getListUser($request, $customerID);
            }
            $response = $this->customResponse->getSuccessResponse(
                $result,
                $total = $countTotal,
                $message = trans('attributes.success'),
                null,
                $statusCode = Response::HTTP_OK,
                null
            );
            Log::debug("Get user list successfully");
            return $this->customResponse->sendResponse($response);
        } catch (\Exception $e) {
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
            return $this->customResponse->sendResponse($response);
        }

    }

}



