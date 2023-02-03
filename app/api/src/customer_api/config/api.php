<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API configurations
    |--------------------------------------------------------------------------
    */
    'base_uri' => env('API_ENDPOINT', ''),

    'timeout' => 240,

    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],

];
