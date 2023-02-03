<?php

namespace App\Support\Base\Api;

use App\Support\Base\Request as BaseRequest;
use App\Support\Base\Response as BaseResponse;
use App\Support\Base\Api as BaseApi;

class CustomerApi extends BaseApi
{
    /**
     * Call API to get list customers
     *
     * @param array $params
     * @return BaseResponse
     */
    public function getList(array $params = []) : BaseResponse
    {
        //set default params for this API
        $defaults = [];

        foreach ($params as $key => $value) {
            \Arr::set($defaults, $key, $value);
        }

        $request = new BaseRequest('GET', 'https://testapi.io/api/huanvu/base/customers/list', $defaults);

        return $this->client->send($request);
    }
}
