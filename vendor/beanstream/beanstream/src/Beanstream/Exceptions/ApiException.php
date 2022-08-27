<?php

namespace Beanstream\Exceptions;

/**
 * ApiException class
 */
class ApiException extends Exception
{
    protected $response;

    public function __construct($message, $code = 0, $response = false)
    {
        $this->response = $response;

        parent::__construct($message, $code);
    }

    /**
     * Gets the Exception message
     * @link https://php.net/manual/en/exception.getmessage.php
     * @return string the Exception message as a string.
     * @since 5.1.0
     */
    final public function getResponse() {
        return $this->response;
    }
}
