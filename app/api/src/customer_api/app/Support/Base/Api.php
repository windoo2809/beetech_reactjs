<?php

namespace App\Support\Base;

use App\Support\Base\Client as BaseClient;

class Api
{
    /**
     * @var BaseClient
     */
    protected $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new BaseClient();
    }
}
