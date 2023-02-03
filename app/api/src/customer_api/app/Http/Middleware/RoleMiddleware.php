<?php

namespace App\Http\Middleware;

use App\Common\CodeDefinition;
use Illuminate\Support\Facades\Log;

use Closure;
use Illuminate\Http\Response;
use App\Common\CustomResponse;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware {

    public function handle($request, Closure $next, $role) {
        $customResponse = new CustomResponse;
        $user = Auth::user();

        $roleList = explode('|', $role);

        if (!$this->checkUserRole($user, $roleList)) {
            Log::debug("User role can't access to this resource");
            $message = __('attributes.403_forbidden');
            $response = $customResponse->getErrorResponse([], $message, Response::HTTP_FORBIDDEN);
            return $customResponse->sendResponse($response);
        }
        
        return $next($request);
    }

    /**
     * check user permission
     *
     * @param $user
     * @param $roleList
     * @return bool
     */
    private function checkUserRole($user, $roleList) {

        $userRole = $user->role;

        // check user permission
        foreach ($roleList as $role) {
            if (intval($userRole) === intval($role)) {
                return true;
            }
        }
        return false;
    }
}