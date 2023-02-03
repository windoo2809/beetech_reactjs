<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommonLogBegin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $requestPayload = $request->all();
        $key = "password";
        if (array_key_exists($key, $requestPayload)) {
          $requestPayload[$key] = str_repeat("*", strlen($requestPayload[$key]));
        }
        Log::info(json_encode($requestPayload, JSON_UNESCAPED_UNICODE));
        return $next($request);
    }
}
