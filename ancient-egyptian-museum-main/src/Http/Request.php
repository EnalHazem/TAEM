<?php

namespace AncientEgyptianMuseum\Http;

use AncientEgyptianMuseum\src\Support\Arr;
use AncientEgyptianMuseum\src\Support\Str;

/**
 * Request Class
 * Handles HTTP request data
 */
class Request
{
    /**
     * Get the current request path
     *
     * @return string
     */
    public function path()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        return str_contains($path, '?') ? explode('?', $path)[0] : $path;
    }

    /**
     * Get the current request HTTP method
     *
     * @return string
     */
    public function method()
    {
        return Str::lower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Get all request data
     *
     * @return array
     */
    public function all()
    {
        return array_merge($_GET, $_POST, $_FILES);
    }

    /**
     * Get only specified keys from request data
     *
     * @param array|string $keys
     * @return array
     */
    public function only($keys)
    {
        return Arr::only($this->all(), is_array($keys) ? $keys : func_get_args());
    }

    /**
     * Get a specific key from request data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->all(), $key, $default);
    }
    
    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               Str::lower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if request has a specific input
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->all(), $key);
    }
    
    /**
     * Get request input with sanitization
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        $value = $this->get($key, $default);
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }
}


/*
namespace AncientEgyptianMuseum\Http;

use AncientEgyptianMuseum\Support\Arr;
use AncientEgyptianMuseum\Support\Str;

class Request
{
    public function path()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        return str_contains($path, '?') ? explode('?', $path)[0] : $path;
    }

    public function method()
    {
        return Str::lower($_SERVER['REQUEST_METHOD']);
    }

    public function all()
    {
        return $_REQUEST;
    }

    public function only($keys)
    {
        return Arr::only($this->all(), $keys);
    }

    public function get($key)
    {
        return Arr::get($this->all(), $key);
    }
}*/