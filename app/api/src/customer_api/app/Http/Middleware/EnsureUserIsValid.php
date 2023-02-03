<?php

namespace App\Http\Middleware;

use App\Common\CodeDefinition;
use App\Dao\DaoConstants;
use App\Dao\MultiTable\CuUserMultiDao;
use App\Dao\SingleTable\CuCustomerOptionDao;
use App\Dao\SingleTable\CuUserDao;
use App\Services\AuthService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth as FireBaseAuth;
use App\Common\CustomResponse;
use Illuminate\Http\Response;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use App\Dao\MultiTable\CuCustomerBranchMultiDao;

class EnsureUserIsValid
{

    protected AuthService $authService;
    

    public function __construct(
        FireBaseAuth $firebaseAuth,
        AuthService $authService,
     
    )
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $customResponse = new CustomResponse();

        $accessToken = $request->bearerToken();
        if (!$accessToken) {
            Log::error("The request does not include token. \$accessToken=NULL");
            $response = $customResponse->getErrorResponse([], trans('attributes.401_unauthorized'), Response::HTTP_UNAUTHORIZED);
            return $customResponse->sendResponse($response);
        }

        // Get user information.
        $user = null;
        $loginId = null;
        //get usser

        try {
            Auth::login($user);
        } catch (\Exception $e) {
            Log::error($e);
            $response = $customResponse->getErrorResponse([], trans("attributes.500_internal_server_error"), Response::HTTP_INTERNAL_SERVER_ERROR);
            return $customResponse->sendResponse($response);
        }

        return $next($request);
    }
}
