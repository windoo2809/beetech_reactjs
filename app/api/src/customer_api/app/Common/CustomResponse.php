<?php

namespace App\Common;

use Symfony\Component\HttpFoundation\Response as HTTPCODERESPONSE;
use Illuminate\Support\Facades\Log;

class CustomResponse
{
    const RESPONSE_DATA = 'data';
    const RESPONSE_TOTAL = 'total';
    const RESPONSE_MESSAGE = 'message';
    const RESPONSE_STATUS_CODE = 'status';
    const RESPONSE_ERRORS = 'errors';
    const RESPONSE_ROUTE = 'page';
    const RESPONSE_ACCESS_TOKEN = 'access_token';
    const RESPONSE_TOKEN = 'token';
    const RESPONSE_TOTAL_HIDE = -1;
    const RESPONSE_EXCEPTION_MESS = 'exception_mess';

    const ERRORCODE_E400001 = 'E400001';
    const ERRORCODE_E400002 = 'E400002';
    const ERRORCODE_E400023 = 'E400023';

    /**
     * Customize the return information.
     * @param $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($response)
    {
        $result = [];

        if (array_key_exists(self::RESPONSE_ROUTE, $response) && $response[self::RESPONSE_ROUTE]){
            $result[self::RESPONSE_ROUTE] = $response[self::RESPONSE_ROUTE];
        }

        if (array_key_exists(self::RESPONSE_MESSAGE, $response)){
            $result[self::RESPONSE_MESSAGE] = $response[self::RESPONSE_MESSAGE];
        }

        if (array_key_exists(self::RESPONSE_DATA, $response)){
            if (!is_null($response[self::RESPONSE_DATA])) {
                $result[self::RESPONSE_DATA] = $response[self::RESPONSE_DATA];
            }

            if (array_key_exists(self::RESPONSE_TOTAL, $response)) {
                $result[self::RESPONSE_TOTAL] = $response[self::RESPONSE_TOTAL];
            }

        } elseif (array_key_exists(self::RESPONSE_ERRORS, $response)) {
            if (array_key_exists(self::RESPONSE_ERRORS, $response) && count($response[self::RESPONSE_ERRORS]) !== 0) {
                $result[self::RESPONSE_ERRORS] = $response[self::RESPONSE_ERRORS];
            }

        }

        if (array_key_exists(self::RESPONSE_ACCESS_TOKEN, $response) && $response[self::RESPONSE_ACCESS_TOKEN]){
            $result[self::RESPONSE_ACCESS_TOKEN] = $response[self::RESPONSE_ACCESS_TOKEN];
        }

        if (array_key_exists(self::RESPONSE_TOKEN, $response) && $response[self::RESPONSE_TOKEN]){
            $result[self::RESPONSE_TOKEN] = $response[self::RESPONSE_TOKEN];
        }

        $exceptionMess = '';

        if (array_key_exists(self::RESPONSE_EXCEPTION_MESS, $response)) {
            $exceptionMess = $response[self::RESPONSE_EXCEPTION_MESS];
        }


        if (array_key_exists(self::RESPONSE_STATUS_CODE, $response)){

            $result[self::RESPONSE_STATUS_CODE] = $response[self::RESPONSE_STATUS_CODE];

            if ($response[self::RESPONSE_STATUS_CODE] ==  HTTPCODERESPONSE::HTTP_OK) {
                Log::info($response[self::RESPONSE_STATUS_CODE]);
            } else {
                if(!empty($exceptionMess)) {
                    Log::error($exceptionMess);
                }
            }
        }

        return response()->json($result, $response[self::RESPONSE_STATUS_CODE]);
    }

    /**
     * Customize the return information on error.
     * @param array $errors
     * @param null $message
     * @param int $statusCode
     * @return array
     */
    public function getErrorResponse($errors = [], $message = null, $statusCode = HTTPCODERESPONSE::HTTP_FORBIDDEN, $exceptionMess = null)
    {
        $aryReturn = [];
        $aryReturn[self::RESPONSE_STATUS_CODE] = $statusCode;
        if ($errors !== null) {
            $aryReturn[self::RESPONSE_ERRORS] = $errors;
        }

        if ($message !== null) {
            $aryReturn[self::RESPONSE_MESSAGE] = $message;
        }

        if ($exceptionMess !== null) {
            $aryReturn[self::RESPONSE_EXCEPTION_MESS] = $exceptionMess;
        }

        return $aryReturn;
    }

    /**
     * Customize the return information on success.
     * @param array $data
     * @param int $total
     * @param null $message
     * @param null $page
     * @param int $statusCode
     * @return array
     */
    public function getSuccessResponse($data = [], $total = 0, $message = null, $page = null, $statusCode = HTTPCODERESPONSE::HTTP_OK, $accessToken = null, $token = null)
    {
        $aryReturn = [];
        $aryReturn[self::RESPONSE_STATUS_CODE] = $statusCode;
        if ($data !== null) {
            $aryReturn[self::RESPONSE_DATA] = $data;
        }

        if ($total !== null && $total != self::RESPONSE_TOTAL_HIDE) {
            $aryReturn[self::RESPONSE_TOTAL] = $total;
        }

        if ($message !== null) {
            $aryReturn[self::RESPONSE_MESSAGE] = $message;
        }

        if ($page !== null) {
            $aryReturn[self::RESPONSE_ROUTE] = $page;
        }

        if ($accessToken !== null) {
            $aryReturn[self::RESPONSE_ACCESS_TOKEN] = $accessToken;
        }
        if ($token !== null) {
            $aryReturn[self::RESPONSE_TOKEN] = $token;
        }

        return $aryReturn;
    }
}
