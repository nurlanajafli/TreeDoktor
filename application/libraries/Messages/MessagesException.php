<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class MessagesException extends \Exception
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

    protected $response;

    /**
     * Constructor
     * @param $message
     * @param  int  $code
     * @param  bool|array  $response
     */
    public function __construct($message, $code = 0, $response = false)
    {
        $this->message = $message;

        $this->code = $code;

        $this->response = $response;

        parent::__construct($this->message, $this->code);
    }

    public function __toString()
    {
        return $this->message . " (code: " . $this->code . ")";
    }

    public function __toArray()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'response' => $this->response
        ];
    }

    /**
     * Gets the Exception message
     * @link https://php.net/manual/en/exception.getmessage.php
     * @return string the Exception message as a string.
     * @since 5.1.0
     */
    final public function getResponse()
    {
        return $this->response;
    }
}
