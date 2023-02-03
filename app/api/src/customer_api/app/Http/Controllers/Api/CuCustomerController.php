<?php

namespace App\Http\Controllers\Api;

use App\Common\CustomResponse;
use App\Http\Controllers\Api\Controller as ApiController;
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

}
