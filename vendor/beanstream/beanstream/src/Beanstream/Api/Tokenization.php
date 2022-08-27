<?php

namespace Beanstream\Api;

use Beanstream\Http\Configuration;

/**
 * Tokenization from Bambora
 *
 * @author Vincent Wilkie
 */
class Tokenization extends Api
{
    /**
     * Create a new tokenization instance.
     *
     * @param  string                          $apiKey
     * @param  \Beanstream\Http\Configuration  $config
     * @return void
     */
    public function __construct($apiKey, Configuration $config)
    {
        parent::__construct($apiKey, $config);
    }

    public function tokenizeCreditCard($data = [])
    {
        $url = $this->endpoints->getTokenizationUrl();
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }
}
