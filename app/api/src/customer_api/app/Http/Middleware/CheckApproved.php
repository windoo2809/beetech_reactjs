<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\Common\CustomResponse;
use Illuminate\Support\Facades\Auth;

class CheckApproved {

    public function handle($request, Closure $next) {
        $customResponse = new CustomResponse;

        $user = Auth::user();
        
        if (empty($user->approval)) {
            $message = __('attributes.400_bad_request');
            $response = $customResponse->getErrorResponse([], $message, Response::HTTP_BAD_REQUEST);
            return $customResponse->sendResponse($response);
        }

        return $next($request);
    }
}