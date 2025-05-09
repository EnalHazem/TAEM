<?php

namespace AncientEgyptianMuseum\Http;

/**
 * Response Class
 * Handles HTTP responses
 */
class Response
{
    /**
     * Set HTTP status code
     *
     * @param int $code
     * @return $this
     */
    public function setStatusCode(int $code)
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Redirect back to previous page
     *
     * @return $this
     */
    public function back()
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        return $this;
    }
    
    /**
     * Redirect to a specific path
     *
     * @param string $path
     * @return $this
     */
    public function redirect($path)
    {
        header("Location: {$path}");
        return $this;
    }
    
    /**
     * Send a JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public function json($data, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Output content with specific content type
     *
     * @param string $content
     * @param string $contentType
     * @return void
     */
    public function output($content, $contentType = 'text/html')
    {
        header("Content-Type: {$contentType}");
        echo $content;
        exit;
    }
    
    /**
     * Return content with HTML content type
     *
     * @param string $content
     * @param int $statusCode
     * @return void
     */
    public function html($content, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->output($content, 'text/html');
    }
    
    /**
     * Add a header to the response
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function withHeader($name, $value)
    {
        header("{$name}: {$value}");
        return $this;
    }
}

/**namespace SecTheater\Http;

class Response
{
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function back()
    {
        header('Location:' . $_SERVER['HTTP_REFERER']);

        return $this;
    }
}*/