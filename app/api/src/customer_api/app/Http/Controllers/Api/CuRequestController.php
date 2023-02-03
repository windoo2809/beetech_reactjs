<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller as ApiController;
use App\Http\Requests\Request;
use App\Common\CustomResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuRequestController extends ApiController
{
    /**
     * @var cuRequestService
     */
    protected $cuRequestService;
    protected $customResponse;
}
