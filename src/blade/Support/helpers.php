<?php

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @return mixed
     */
    function tap($value, Closure $callback)
    {
        $callback($value);

        return $value;
    }
}

if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return \Swoft\Support\Arr::except($array, $keys);
    }
}

if (! function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  \Swoft\Support\Contracts\Htmlable|string  $value
     * @param  bool  $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof \Swoft\Support\Contracts\Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('blade')) {
    /**
     * blade模板引擎
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Swoft\Blade\Contracts\View
     */
    function blade(string $view, array $data = [], $mergeData = [])
    {
        return \Swoft::getBean('blade.view')->make($view, $data, $mergeData);
    }
}

if (! function_exists('blade_factory')) {
    /**
     * blade模板引擎工厂对象
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Swoft\Blade\Factory
     */
    function blade_factory()
    {
        return \Swoft::getBean('blade.view');
    }
}

if (! function_exists('filesystem')) {
    /**
     * @return \Swoft\Support\Filesystem
     */
    function filesystem(): \Swoft\Support\Filesystem
    {
        static $instance;

        return $instance ?: ($instance = new \Swoft\Support\Filesystem);
    }
}
