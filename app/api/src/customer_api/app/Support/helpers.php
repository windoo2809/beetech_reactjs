<?php

if (! function_exists('constants')) {
    /**
     * Function return constant value by specific key.
     *
     * @param null $key
     * @return \Illuminate\Config\Repository|mixed
     */
    function constants($key = null)
    {
        $value = config('constants.' . $key);
        if (isset($value)) {
            $filter = function($items) use (&$filter) {
                if(is_array($items)){
                    $data = [];
                    foreach ($items as $key => $value) {
                        $data[$key] = is_array($value) ? call_user_func($filter, $value) : __($value, [], $value);
                    }
                    return $data;
                }
                return __($items, [], $items);
            };
            return $filter($value);
        }
        return null;
    }
}