<?php

namespace App\Support\Base;

class Request
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var array
     */
    protected $formParams;

    /**
     * @var bool
     */
    protected $direction;

    /**
     * Constructor
     */
    public function __construct(string $method, string $uri, array $formParams, bool $ggCheck, bool $direction = false)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->formParams = $formParams;
        $this->ggCheck = $ggCheck;
        $this->direction = $direction;
    }

    public function method() : string
    {
        return $this->method;
    }

    public function uri() : string
    {
        return $this->uri;
    }

    public function formParams() : array
    {
        return $this->formParams;
    }

    public function ggCheck() : bool
    {
        return $this->ggCheck;
    }

    public function getDirection() : bool
    {
        return $this->direction;
    }
}
