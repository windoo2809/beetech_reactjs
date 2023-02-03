<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];
    
    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }
        
        $chars = '\s　';
        if (is_string($value) && $value) {
            $value = preg_replace("/^[$chars]+/u", '', $value);
            $value = preg_replace("/[$chars]+$/u", '', $value);
        }
        
        return $value;
    }
}
