<?php

namespace App\Http\Controllers\Api;

use App\Common\CustomResponse;
use App\Http\Controllers\Api\Controller as ApiController;
use App\Http\Requests\CuProject\CuProjectListApplyToApproveRequests;
use App\Http\Requests\CuProject\ProjectListRequest;
use App\Services\Interfaces\CuProjectServiceInterface;
use App\Http\Requests\CuProject\CreateProjectRequest;
use App\Http\Requests\CuProject\UpdateProjectRequest;
use App\Http\Requests\CuProject\UpdateProjectRequestRequest;
use App\Http\Requests\CuProject\ShowProjectRequest;
use App\Http\Requests\CuProject\ExtendProjectRequest;
use App\Http\Requests\CuProject\ListProjectRequest;
use App\Http\Requests\CuProject\ProgressStatusRequest;
use App\Http\Requests\CuProject\ProjectListByEstimateRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CuProjectController extends ApiController
{
    protected $customResponse;

    /**
     * @var cuProjectService
     */
    protected $cuProjectService;

    /**
     * CuMessageController constructor.
     *
     * @param CuProjectServiceInterface $cuProjectService
     */
    public function __construct(CuProjectServiceInterface $cuProjectService, CustomResponse $customResponse)
    {
        parent::__construct();
        $this->customResponse = $customResponse;
        $this->cuProjectService = $cuProjectService;
    }

    /**
     * Get a list of construction information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(ListProjectRequest $request)
    {

        try {
            $user = Auth::user();
            $projectCount = $this->cuProjectService->countProject($request, $user);

            $result = [];

            if ($projectCount > 0) {
                $detailCount = $this->cuProjectService->detailCount($request->all(), $user);
                $projectData = $this->cuProjectService->listProject($request, $user);
                $result = [
                    'data' => $projectData,
                    'parking_count' => $detailCount['parking_count'],
                    'request_count' => $detailCount['request_count'],
                    'count' => $detailCount['project_count'],
                ];
            }

            $response = $this->customResponse->getSuccessResponse(
                $result,
                $projectCount,
                $message = trans('attributes.success')
            );

            Log::debug("Get project list sucessfully");
        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * get construction details.
     *
     * @param int $projectId
     *
     * @return JsonResponse
     */
    public function show(ShowProjectRequest $request)
    {
        try {
            $user = Auth::user();
            $result = $this->cuProjectService->getProjectInfo($request->get('project_id'), $user);
            if ($result['success']) {
                $response = $this->customResponse->getSuccessResponse(
                    $result['data'],
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    trans('attributes.success')
                );
                Log::debug("Get project information successfully");
            } else {
                $message = __("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse(null, $message, Response::HTTP_FORBIDDEN);
                Log::error("You don't have access to this project. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }

        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * create project information
     *
     * @param CreateProjectRequest $request
     * @return mixed
     */
    public function store(CreateProjectRequest $request)
    {
        try {
            $result =  $this->cuProjectService->create($request);
            if ($result['success']) {
                $message =  trans('attributes.success');
                $responseData = $result['data'];
                $responseData['status'] = Response::HTTP_OK;
                $responseData['message'] = $message;

                Log::debug("Create project information successfully");
                return response()->json($responseData, Response::HTTP_OK);
            }
            else {
                if (!empty($result['messageCatch'])) {
                    $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode'], $result['messageCatch']);
                } else if(!empty($result['messageParking']))
                {
                    $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode'], $result['messageParking']);
                } else {
                    $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode']);
                }

                Log::error('Create project information failedl $result='. json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * update project information
     *
     * @param UpdateProjectRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProjectRequest $request)
    {
        try {
            $result = $this->cuProjectService->update($request->all());

            if ($result) {
                $response = $this->customResponse->getSuccessResponse(
                    true,
                    CustomResponse::RESPONSE_TOTAL_HIDE,
                    trans('attributes.success')
                );
                Log::debug("Update project information successfully");
            } else {
                $message = __("attributes.403_forbidden");
                $response = $this->customResponse->getErrorResponse(null, $message, Response::HTTP_FORBIDDEN);
                Log::error("You don't have permission to update this project. \$result=". $result);
            }

        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * get application list
     * @param CuProjectListApplyToApproveRequests $requests
     * @return JsonResponse
     */
    public function getListApplyToApprove(CuProjectListApplyToApproveRequests $requests)
    {
        try {
            $totalRecord = $this->cuProjectService->getCountAllApplyToApprove($requests);
            $result = [];
            if($totalRecord > 0) {
                $result = $this->cuProjectService->showApplyToApprove($requests);
            }

            $response = $this->customResponse->getSuccessResponse(
                $result,
                $totalRecord,
                $message = trans('attributes.success'),
                null,
                Response::HTTP_OK
            );
            Log::debug("Get list application successfully");
        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * update project information
     * @param UpdateProjectRequestRequest $request
     * @return JsonResponse
     */

    public function updateProjectRequest(UpdateProjectRequestRequest $request)
    {
        $result = $this->cuProjectService->updateProjectRequest($request);

        if ($result['success']) {
            $response = $this->customResponse->getSuccessResponse(
                true,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                trans('attributes.success')
            );
            Log::debug("Update project information successfully");
        } else {
            if (empty($result['errorCatch'])) {
                $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode'], !empty($result['message_log']) ? $result['message_log'] : "" );
            }else {
                $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode'], $result['errorCatch']);
            }
            Log::error("Update project information failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * Extension of project information.
     *
     * @param ExtendProjectRequest $request
     * @return mixed
     */
    public function extendedProject(ExtendProjectRequest $request)
    {
        try {
            $result =  $this->cuProjectService->extendProject($request);
            if ($result['success']) {
                $message =  trans('attributes.success');
                $responseData = $result['data'];
                $responseData['status'] = Response::HTTP_OK;
                $responseData['message'] = $message;

                Log::debug("Extend project information successfully");
                return response()->json($responseData, Response::HTTP_OK);
            }
            else {
                $messForLog = !empty($result['messParking']) ? $result['messParking'] : (!empty($result['messCatch']) ? $result['messCatch']: "");
                $response = $this->customResponse->getErrorResponse([], $result['message'], $result['statusCode'], $messForLog);
                Log::error("Extend project information failed. \$result=". json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (Exception $e) {
            $response = (new CustomResponse())->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }
        return $this->customResponse->sendResponse($response);
    }

    /**
     * get progress status
     *
     * @param ProgressStatusRequest $request
     * @return JsonResponse
     */

    public function getProgressStatus (ProgressStatusRequest $request)
    {
        try {
            $progressStatus = $this->cuProjectService->getProgressStatus($request->all());
            $response = $this->customResponse->getSuccessResponse(
                ['progress_status' => $progressStatus],
                CustomResponse::RESPONSE_TOTAL_HIDE,
                trans('attributes.success')
            );
            Log::debug("Get progress status successfully");
        } catch (\Exception $e) {
            $response = $this->customResponse->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR, "");
            Log::error($e);
        }

        return $this->customResponse->sendResponse($response);
    }

    /**
     * Get Project List
     *
     * @param ProjectListRequest $request
     * @return JsonResponse
     */
    public function getProjectList(ProjectListRequest $request)
    {
        try {
            $user = Auth::user();

            $result = $this->cuProjectService->getDataProjectList($request->all(), $user);

            $response = $this->customResponse->getSuccessResponse(
                $result,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                __('attributes.success'),
            );

            Log::debug("Get project list successfully");

        } catch (Exception $e) {
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
     * Get Project List by estimate id
     *
     * @param ProjectListByEstimateRequest $request
     * @return JsonResponse
     */
    public function getProjectListByEstimateID(ProjectListByEstimateRequest $request)
    {
        try {

            $user = Auth::user();
            $result = $this->cuProjectService->getProjectListByEstimateID($request->input('estimate_id'),  $user);

            $response = $this->customResponse->getSuccessResponse(
                $result,
                CustomResponse::RESPONSE_TOTAL_HIDE,
                __('attributes.success'),
            );

            Log::debug("Get project list by estimate successfully");

        } catch (Exception $e) {
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
}
