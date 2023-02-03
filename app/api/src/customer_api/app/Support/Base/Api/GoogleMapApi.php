<?php

namespace App\Support\Base\Api;

use App\Support\Base\Request as BaseRequest;
use App\Support\Base\Response as BaseResponse;
use App\Support\Base\Api as BaseApi;
use Illuminate\Http\Response;

class GoogleMapApi extends BaseApi
{
    /**
     * Google result
     */
    const GOOGLE_NOT_FOUND = 'NOT_FOUND';
    const GOOGLE_ZERO_RESULTS = 'ZERO_RESULTS';
    const GOOGLE_MAX_WAYPOINTS_EXCEEDED = 'MAX_WAYPOINTS_EXCEEDED';
    const GOOGLE_MAX_ROUTE_LENGTH_EXCEEDED = 'MAX_ROUTE_LENGTH_EXCEEDED';
    const GOOGLE_OVER_DAILY_LIMIT = 'OVER_DAILY_LIMIT';
    const GOOGLE_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';
    const GOOGLE_REQUEST_DENIED = 'REQUEST_DENIED';
    const GOOGLE_INVALID_REQUEST = 'INVALID_REQUEST';
    const GOOGLE_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    /**
     * Call API to get list customers
     *
     * @param array $params
     * @return BaseResponse
     */
    public function getAddressInfo(array $params = []) : BaseResponse
    {
        //set default params for this API
        $defaults = [];

        foreach ($params as $key => $value) {
            \Arr::set($defaults, $key, $value);
        }

        if (env('GOOGLE_MAP_URL', '') === '') {
            $message = trans('attributes.cu_parking.message.GOOGLE_REQUEST_DENIED');
            return abort( response(['code' => GoogleMapApi::GOOGLE_REQUEST_DENIED, 'message' => $message], Response::HTTP_BAD_GATEWAY) );
        }

        $request = new BaseRequest('GET', env('GOOGLE_MAP_URL', ''), $defaults, true);

        return $this->client->send($request);
    }

    /**
     * @param array $params
     * @return BaseResponse
     */
    public function parkingRouteSearch(array $params = []) : BaseResponse
    {
        //set default params for this API
        $defaults = [];

        foreach ($params as $key => $value) {
            \Arr::set($defaults, $key, $value);
        }

        if (env('GOOGLE_MAP_DIRECTION', '') === '') {
            $message = trans('attributes.cu_parking.message.GOOGLE_REQUEST_DENIED');
            abort( response(['code' => GoogleMapApi::GOOGLE_REQUEST_DENIED, 'message' => $message], Response::HTTP_BAD_GATEWAY) );
        }

        $request = new BaseRequest('GET', env('GOOGLE_MAP_DIRECTION', ''), $defaults, true, true);

        return $this->client->send($request);
    }
}
