<?php

namespace App\Support\Base;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use App\Support\Base\Request as BaseRequest;
use App\Support\Base\Response as BaseResponse;
use Illuminate\Support\Facades\Log;

class Client
{
    protected $baseUri;
    protected $timeout;
    protected $headers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUri = config('api.base_uri');
        $this->timeout = config('api.timeout');
        $this->headers = config('api.headers');
    }

    /**
     * Send request to API server and return response
     *
     * @param BaseRequest $request
     * @return array
     */
    public function send(BaseRequest $request) : BaseResponse
    {
        try {
            if (config('app.debug')) {
                Log::info(json_encode($request->formParams()));
            }

            $options = [
                'headers' => $this->headers ?: [],
                'timeout' => $this->timeout,
            ];

            if ($request->ggCheck()) {
                $options['query'] = $request->formParams();
            } else {
                $options['json'] = $request->formParams();
            }

            $response = $this->getClient()->request(
                $request->method(),
                $this->baseUri . $request->uri(),
                $options
            );


            if ($request->ggCheck()) {
                $responseBody = json_decode($response->getBody(), true) ?: [];

                if ($request->getDirection()) {
                    return new BaseResponse(
                        $responseBody['status'] ?? '',
                        $responseBody['error_message'] ?? '',
                        $responseBody
                    );
                }

                return new BaseResponse(
                    $responseBody['status'] ?? '',
                    $responseBody['error_message'] ?? '',
                    $responseBody['results'] ?? []
                );
            }

            $responseBody = json_decode($response->getBody()->getContents(), true) ?: [];

            return new BaseResponse(
                $responseBody['success'] ?? false,
                $responseBody['message'] ?? '',
                $responseBody['data'] ?? []
            );
        } catch (RequestException $e) {
            Log::error( $e);

            if ( ! $e->getCode()) {
                abort(\Illuminate\Http\Response::HTTP_BAD_GATEWAY);
            }

            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true) ?: [];

            switch ($e->getCode()) {
                case \Illuminate\Http\Response::HTTP_FORBIDDEN:
                case \Illuminate\Http\Response::HTTP_NOT_FOUND:
                case \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR:
                    abort($e->getCode(), $responseBody['message']);
                    break;
                default:
                    return new BaseResponse(
                        $responseBody['success'] ?? '',
                        $responseBody['message'] ?? '',
                        $responseBody['errors'] ?? []
                    );
            }
        }
    }

    /**
     * Build client for requesting server
     *
     * @return GuzzleClient
     */
    protected function getClient() : GuzzleClient
    {
        return new GuzzleClient([
            'base_uri' => $this->baseUri,
            'timeout'  => $this->timeout,
            'headers' => $this->headers,
        ]);
    }
}
