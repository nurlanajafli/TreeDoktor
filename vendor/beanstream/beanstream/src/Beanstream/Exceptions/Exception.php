<?php

namespace Beanstream\Exceptions;

/**
 * Exception class
 *
 * Error code zero (0) corresponds to PHP specific errors
 *
 * Positive error codes correspond to the Beanstream API
 * @link http://developer.beanstream.com/documentation/take-payments/errors/
 * @link http://developer.beanstream.com/documentation/analyze-payments/errors/
 * @link http://developer.beanstream.com/documentation/analyze-payments/api-messages/
 * @link http://developer.beanstream.com/documentation/tokenize-payments/errors/
 *
 * Negative error codes correspond to CURL
 * @link https://curl.haxx.se/libcurl/c/libcurl-errors.html
 *
 * @author Vincent Wilkie
 */
class Exception extends \Exception
{
    /**
     * Exception message
     *
     * @var string $message holds the human-readable error message string
     */
    protected $message;

    /**
     * Exception error code
     *
     * @var int $code holds the error message code (0=PHP, Positive=Beanstream API, Negative=cURL)
     */
    protected $code;

    /**
     * Constructor
     */
    public function __construct($message, $code = 0)
    {
        $this->message = $message;

        $this->code = $code;

        parent::__construct($this->message, $this->code);
    }
}
