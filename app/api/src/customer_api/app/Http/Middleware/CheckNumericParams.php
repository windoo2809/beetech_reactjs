<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\Common\CustomResponse;

class CheckNumericParams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $params = $request->route()->parameters();

        if (empty($params) || !is_array($params)) {
            abort(Response::HTTP_BAD_REQUEST, CustomResponse::ERRORCODE_E400001);
        }

        foreach ($params as $key => $value) {
            if (!is_numeric($value)) {
                abort(Response::HTTP_BAD_REQUEST, CustomResponse::ERRORCODE_E400002);
            }
        }

        return $next($request);
    }
}