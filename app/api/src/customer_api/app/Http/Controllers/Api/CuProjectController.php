<?php

namespace App\Http\Controllers\Api;

use App\Common\CustomResponse;
use App\Http\Controllers\Api\Controller as ApiController;
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


}
