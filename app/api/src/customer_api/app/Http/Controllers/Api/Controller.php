<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as AppController;

class Controller extends AppController
{
    /**
     * Use middlewares
     */
    public function __construct()
    {
        //$this->middleware(['auth.jwt']);
    }
}
