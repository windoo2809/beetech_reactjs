<?php

namespace App\Http\Controllers\Api;

use App\Common\CodeDefinition;
use App\Common\CustomResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CuUser\ChangePasswordRequest;
use App\Http\Requests\CuUser\CreateUserRequest;
use App\Http\Requests\CuUser\CuUserRequest;
use App\Http\Requests\CuUser\CuUserShowRequest;
use App\Http\Requests\CuUser\ImportCsvRequest;
use App\Http\Requests\CuUser\ResetPasswordRequest;
use App\Http\Requests\CuUser\UpdateCuUserRequest;
use App\Http\Requests\CuUser\SettingPasswordLoginFirstTimeRequest;
use App\Services\AuthService;
use App\Services\CuUserService;
use App\Services\CuUserTokenService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuUserController extends Controller
{
    /**
     * @var customResponse
     */
    protected $customResponse;
    protected $cuUserService;
    protected $authService;

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
     * resetting password
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetSettingPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->cuUserService->updatePasswordResetting($request);
            if ($result) {
                DB::commit();
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    Response::HTTP_OK,
                    $result['access_token']
                );
                Log::debug("Reset password successfully");
            } else {
                DB::rollBack();
                $message = trans("attributes.500_internal_server_error");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
                Log::error("Reset password failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /** update password
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword ( ChangePasswordRequest $request )
    {
        DB::beginTransaction();
        try {
            $userId = Auth::user()->user_id;
            $result = $this->cuUserService->changePassword($request, $userId);
            if ($result) {
                DB::commit();
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    Response::HTTP_OK,
                    $result['access_token']
                );
                Log::debug("update password successfully");
            } else {
                DB::rollBack();
                $message = trans('validation.custom.compare.password');
                $error = [
                    'errorcode' => 'E400026',
                    'message' => $message
                ];
                $response = $this->customResponse->getErrorResponse($error, trans('validation.message_422'),Response::HTTP_UNPROCESSABLE_ENTITY);
                Log::error("update password failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * get user list
     * @param CuUserRequest $request
     * @return JsonResponse
     */
    public function index (CuUserRequest $request) {
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

    /**
     * get user details
     * @param CuUserShowRequest $request
     * @return JsonResponse
     */
    public function show ( CuUserShowRequest $request ) {
        try {
            $result = $this->cuUserService->getDetailUser($request->user_id);
            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    $result,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success')
                );
                Log::debug("Get user information successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
                Log::error("Get user information failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * import user from CSV file
     * @param ImportCsvRequest $request
     * @return JsonResponse
     */
    public function importCsv(ImportCsvRequest $request)
    {
        DB::beginTransaction();
        try {
            $fileBody = $this->cuUserService->importCsvData($request);
            if (!is_array($fileBody)) {
                DB::commit();
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    $total = CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    $statusCode = Response::HTTP_OK,
                    null
                );
                Log::debug("Import user from CSV successfully");
            } else {
                DB::rollBack();
                Log::error("Import user from CSV failed");
                $response = $this->customResponse->getErrorResponse(
                    $fileBody,
                    trans("attributes.422_unprocessable_entity"),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message,
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR);
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Register a new user
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store (CreateUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->cuUserService->createUser( $request );
            if ($result) {
                DB::commit();
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    $total = CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success')
                );
                Log::debug("Create user information successfully");
            } else {
                DB::rollBack();
                $message = trans("attributes.500_internal_server_error");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
                Log::error("Create user information failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);

    }

    /**
     * update user information
     * @param UpdateCuUserRequest $request
     * @return JsonResponse
     * @throws \Kreait\Firebase\Exception\AuthException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function update (UpdateCuUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->cuUserService->updateUser( $request );
        } catch (\Exception $e) {
            DB::rollBack();
            $message = trans("attributes.500_internal_server_error");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
            return $this->customResponse->sendResponse($response);
        }

        if (is_array($result) && $result['statusCode'] == Response::HTTP_UNPROCESSABLE_ENTITY) {
            DB::rollBack();
            $message = trans("validation.custom.exist.user_id");
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            Log::error("The specified customer branch ID does not exist.\$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            return $this->customResponse->sendResponse($response);
        }

        if (is_array($result) && $result['statusCode'] == Response::HTTP_BAD_REQUEST) {
            DB::rollBack();
            $message = trans('validation.' . $result['errorMess']);
            $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_BAD_REQUEST);
            Log::error("An error occurred while updating user information, \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            return $this->customResponse->sendResponse($response);
        }

        DB::commit();
        $response = $this->customResponse->getSuccessResponse(
            [],
            CustomResponse::RESPONSE_TOTAL_HIDE,
            $message = trans('attributes.success'),
            [],
            Response::HTTP_OK
        );
        Log::debug("Update user successfully");
        return $this->customResponse->sendResponse($response);
    }


    /**
     * update first time password
     * @param SettingPasswordLoginFirstTimeRequest $request
     * @return JsonResponse
     * @throws \Kreait\Firebase\Exception\AuthException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function updatePasswordFirstTime(SettingPasswordLoginFirstTimeRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->cuUserService->updatePasswordFirstTime($request);
            if (is_array($result)) {
                DB::commit();
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    Response::HTTP_OK,
                    $result['access_token']
                );
                Log::debug("Update password in first time login successfully");
            } else {
                DB::rollBack();
                $message = trans("attributes.500_internal_server_error");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
                Log::error("Update password in first time login failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

}



