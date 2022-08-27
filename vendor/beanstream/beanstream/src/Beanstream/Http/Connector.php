<?php

namespace Beanstream\Http;

use Beanstream\Exceptions\ApiException;
use Beanstream\Exceptions\ConnectorException;

/**
 * Connector class to handle HTTP requests to the REST API
 *
 * @author Vincent Wilkie
 */
class Connector
{
    /**
     * Base64 encoded passcode for authentication
     *
     * @var string $passCode
     */
    private $passCode;

    /**
     * Constructor
     */
    public function __construct($passCode)
    {
        // Used for authentication against the URL endpoint
        $this->passCode = $passCode;
    }

    /**
     * Sends a request to the specified url endpoint
     *
     * @param string $url URL endpoint the request is sent too
     * @param array|null $data Data to send for a POST request
     * @param string|null $httpMethod HTTP method to use for the curl request
     * @return array                   Parsed API response from the URL endpoint
     * @throws ApiException
     * @throws ConnectorException
     */
    public function sendRequest($url = '', $data = null, $httpMethod = null)
    {
        // Check if the curl extension is loaded
        if (!extension_loaded('curl')) {
            throw new ConnectorException('The cURL extension is required', 0);
        }

        // Init curl via $url endpoint
        $ch = curl_init($url);

        // Set content type and passcode authorization
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Passcode '.$this->passCode
        ]);

        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Check the httpMethod
        if (is_null($httpMethod)) {
            $httpMethod = is_null($data) ? 'GET' : 'POST';
        }

        // Set HTTP method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);

        // If the payload is good to go
        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Execute the curl request
        $result = curl_exec($ch);

        // Curl error
        if (false === $result) {
            throw new ConnectorException(curl_error($ch), -curl_errno($ch));
        }

        // Decode the result
        $decoded = json_decode($result, true);

        // Could not decode the result properly, throw error
        if (is_null($decoded)) {
            throw new ConnectorException('Unexpected response format', 0);
        }

        $apiCode = (int)(isset($decoded['code']) ? $decoded['code'] : 0);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors from the API
        if ($apiCode > 1 || ($httpCode < 200 || $httpCode >= 300)) {
            throw new ApiException($decoded['message'], $apiCode, $decoded);
        }

        curl_close($ch);

        return $decoded;
    }
}
