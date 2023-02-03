<?php

namespace App\Support\Base;

class Response
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct(string $success, string $message, array $data = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

    public function success() : string
    {
        return $this->success;
    }

    public function message() : string
    {
        return $this->message;
    }

    public function data() : array
    {
        return $this->data;
    }
}
