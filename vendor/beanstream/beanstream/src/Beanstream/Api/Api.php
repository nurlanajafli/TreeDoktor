<?php

namespace Beanstream\Api;

use Beanstream\Exceptions\ApiException;
use Beanstream\Http\Configuration;
use Beanstream\Http\Connector;
use Beanstream\Http\Endpoints;

/**
 * Api class
 *
 * @author Vincent Wilkie
 */
class Api
{
    /**
     * The connector implementation.
     *
     * @var \Beanstream\Http\Connector
     */
    protected $connector;

    /**
     * The endpoints implementation.
     *
     * @var \Beanstream\Http\Endpoints
     */
    protected $endpoints;

    /**
     * Create a new api instance
     *
     * @param string        $apiKey
     * @param Configuration $config
     */
    public function __construct($apiKey, Configuration $config)
    {
        if (empty($apiKey)) {
            throw new ApiException('Invalid API Key');
        }

        $this->endpoints = new Endpoints($config->getApiVersion(), $config->getPlatform());

        $this->connector = new Connector(base64_encode($config->getMerchantID().':'.$apiKey));
    }
}
