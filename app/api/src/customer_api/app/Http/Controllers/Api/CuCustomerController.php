<?php

namespace App\Http\Controllers\Api;

use App\Common\CustomResponse;
use App\Http\Controllers\Api\Controller as ApiController;
use App\Http\Requests\CuCustomer\CuCustomerGetListRequest;
use App\Http\Requests\CuCustomer\ShowCustomerRequest;
use App\Services\Interfaces\CuCustomerServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CuCustomerController extends ApiController
{
    /**
     * @var cuCustomerService
     */
    protected $cuCustomerService;
    protected $customResponse;

    /**
     * CuCustomerController constructor.
     *
     * @param CuCustomerServiceInterface $cuCustomerService
     * @param CustomResponse $customResponse
     */
    public function __construct(CuCustomerServiceInterface $cuCustomerService, CustomResponse $customResponse)
    {
        parent::__construct();
        $this->cuCustomerService = $cuCustomerService;
        $this->customResponse = $customResponse;
    }

    /**
     * Get customer details.
     *
     * @param int $customerId
     *
     * @return JsonResponse response
     */
    public function show(ShowCustomerRequest $request)
    {
        try {
            $result = $this->cuCustomerService->getCustomerInfo($request->get('customer_id'));

            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    $result,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    $message = trans('attributes.success'),
                );
                Log::debug("Get customer information successfully");
            } else {
                $message = trans("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
                Log::error("You don't have access to this customer information. \$result=". json_encode($result));
            }
        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * Get a list of customers.
     * @param CuCustomerGetListRequest $request
     * @param $customerId
     * @return mixed
     */
    public function index ( CuCustomerGetListRequest $request )
    {
        try {
            $role = Auth::user()->role;
            $customerLoginId = Auth::user()->customer_login_id;
            $result = [];
            $countTotal = $this->cuCustomerService->getCountCustomerBranch($request->customer_name, $customerLoginId, $role);
            if ($countTotal > 0) {
                $result = $this->cuCustomerService->getListCustomerInfo( $request, $customerLoginId, $role );
            }
            $response = $this->customResponse->getSuccessResponse(
                $result,
                $countTotal,
                $message = count($result) ? trans('attributes.success') : trans('attributes.cu_customer.message_200_no_data')
            );
            Log::debug("Get customer list successfully");
        } catch (\Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }
}
