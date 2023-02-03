<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller as ApiController;
use App\Http\Requests\CuRequest\GetRequestStatusListRequest;
use App\Http\Requests\Request;
use App\Services\Interfaces\CuRequestServiceInterface;
use App\Common\CustomResponse;
use App\Http\Requests\CuRequest\CreateRequestRequest;
use App\Http\Requests\CuRequest\UpdateRequestRequest;
use App\Http\Requests\CuRequest\ShowRequestRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CuRequest\BussinessDayRequest;
use App\Http\Requests\CuRequest\GetRequestEstimateRequest;
use App\Http\Requests\CuRequest\ExtendedRequestRequest;

class CuRequestController extends ApiController
{
    /**
     * @var cuRequestService
     */
    protected $cuRequestService;
    protected $customResponse;

    /**
     * CuMessageController constructor.
     *
     * @param CuRequestServiceInterface $cuRequestService
     */
    public function __construct(CuRequestServiceInterface $cuRequestService, CustomResponse $customResponse)
    {
        parent::__construct();
        $this->cuRequestService = $cuRequestService;
        $this->customResponse = $customResponse;
    }

    /**
     * Get request details.
     *
     * @param ShowRequestRequest $request
     * @return JsonResponse
     */
    public function show(ShowRequestRequest $request)
    {
        try {
            $result = $this->cuRequestService->getRequestInfo($request->get('request_id'));
            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    $result,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success')
                );
                Log::debug("Get request information successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN, $message);
                Log::error("You don't have access to this request information. \$result=". $result);
            }
        } catch (\Exception $e) {

            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * get request information for copy
     * @param ShowRequestRequest $request
     * @return JsonResponse
     */
    public function requestForCopy(ShowRequestRequest $request)
    {
        try {
            $result = $this->cuRequestService->getRequestInfoForCopy($request->get('request_id'));
            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    $result,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    $statusCode = Response::HTTP_OK
                );
                Log::debug("Get request information for copy successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
                Log::error("You don't have access to this request information. \$result=". $result);
            }
        } catch (\Exception $e) {

            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * get request information for extend
     * @param ShowRequestRequest $request
     * @return JsonResponse
     */
    public function requestForExtend(ShowRequestRequest $request)
    {
        try {
            $result = $this->cuRequestService->getRequestInfoForExtend($request->get('request_id'));
            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    $result,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                    null,
                    $statusCode = Response::HTTP_OK
                );
                Log::debug("Get request information for extend successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
                Log::error("You don't have access to this request information. \$result=". $result);
            }
        } catch (\Exception $e) {

            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * create new request
     * @param CreateRequestRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateRequestRequest $request)
    {
        try {
            $user = Auth::user();
            $created = $this->cuRequestService->create($request->all(), $user);

            if (!empty($created)) {
                $response = $this->customResponse->getSuccessResponse(
                    $created,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                );
                DB::commit();
                Log::debug("Create request information successfully");
                return $this->customResponse->sendResponse($response);
            }

            $message = trans("attributes.500_internal_server_error");
            $response =  $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_INTERNAL_SERVER_ERROR);
            Log::error("Create request information failed. \$created=". json_encode($created));

        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * Update the request information.
     * @param UpdateRequestRequest $request
     *
     * @return JsonResponse
     */
    public function update(UpdateRequestRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $updated = $this->cuRequestService->update($request->all(), $user);
            if ($updated) {
                $response = $this->customResponse->getSuccessResponse(
                    [],
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success')
                );
                Log::debug("Update request information successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN, $message);
                Log::error("You don't have permission to update request. \$updated=". $updated);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * get bussiness day
     *
     * @param BussinessDayRequest $request
     * @return JsonResponse
     */
    public function getBussinessDay (BussinessDayRequest $request)
    {
        try {
            $date = $this->cuRequestService->getBussinessDay($request->all());
            $response = $this->customResponse->getSuccessResponse(
                ['date' => $date],
                CustomResponse::RESPONSE_TOTAL_HIDE,
                trans('attributes.success')
            );
            Log::debug("Get bussiness day successfully");
        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * Get Request Estimate
     *
     * @return JsonResponse
     */
    public function getRequestEstimate(GetRequestEstimateRequest $request)
    {
        try {
            $user = Auth::user();

            $data = $this->cuRequestService->getRequestEstimateService($request->all(), $user);

            $response = $this->customResponse->getSuccessResponse(
                $data,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                __('attributes.success')
            );

            Log::debug("Get request estimate successfully");

        } catch (\Exception $e) {
            $response = ( new CustomResponse() )->getErrorResponse(
                [],
                trans("attributes.500_internal_server_error"),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ""
            );

            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * get request status list from v_project_view
     * @param GetRequestStatusListRequest $request
     * @return JsonResponse
     */
    public function getRequestStatusList(GetRequestStatusListRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->cuRequestService->getRequestStatusList($request, $user);
            $response = $this->customResponse->getSuccessResponse(
                $result,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                trans('attributes.success'),
                null,
                Response::HTTP_OK
            );
            Log::debug("Get request status list successfully");
        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }
    
    /**
     * extend request
     * @param ExtendedRequestRequest $request
     * @return JsonResponse
     */
    public function extendedRequest(ExtendedRequestRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->cuRequestService->extendedRequest($request, $user);
            $response = $this->customResponse->getSuccessResponse(
                $result,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                trans('attributes.success'),
                null,
                Response::HTTP_OK
            );
            Log::debug("Extend requestlist successfully");
        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }
}
