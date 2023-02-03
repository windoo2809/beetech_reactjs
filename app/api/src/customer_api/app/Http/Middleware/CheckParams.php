<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckParams
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
        if (empty($request->route()->parameters())) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}