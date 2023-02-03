<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\Common\CustomResponse;
use App\Http\Requests\Request;

class CheckNumericInPath
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
    public function handle($request, Closure $next, $target)
    {
        $customResponse = new CustomResponse;
        $params = $request->route()->parameters();
        
        $target = explode('.', $target);
        $targetTable = $target[0];
        $targetCol = $target[1];
        $listTargetCol = explode('|', $targetCol);

        $listError = [];
        
        foreach($listTargetCol as $col) {

            if (!array_key_exists($col, $params) || (array_key_exists($col, $params) && !isset($params[$col]))) {
                $listError[$col] = [
                    [
                        'errorcode' => CustomResponse::ERRORCODE_E400001,
                        'message' =>  __('validation.errorcode.E400001', ['attribute' => __('attributes.'. $targetTable . '.' . $col)])
                    ]
                ];
            }
        }
        
        if (count($listError)) {
            $message = __('validation.message_422');
            $response = $customResponse->getErrorResponse($listError, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            return $customResponse->sendResponse($response);
        }

        foreach ($params as $key => $value) {
            if (in_array($key, $listTargetCol)) {
                if (!preg_match(Request::REGEX_INT, $value)) {
                    $listError[$key] = [
                        [
                            'errorcode' => CustomResponse::ERRORCODE_E400002,
                            'message' =>  __('validation.errorcode.E400002', ['attribute' => __('attributes.'. $targetTable . '.' . $key)])
                        ]
                    ];
                }
            }
            
        }
        
        if (count($listError)) {
            $message = __('validation.message_422');
            $response = $customResponse->getErrorResponse($listError, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            return $customResponse->sendResponse($response);
        }

        return $next($request);
    }
}