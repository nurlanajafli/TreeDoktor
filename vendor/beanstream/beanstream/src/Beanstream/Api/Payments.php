<?php

namespace Beanstream\Api;

use Beanstream\Http\Configuration;

/**
 * Interaction with payments from Bambora
 *
 * @author Vincent Wilkie
 */
class Payments extends Api
{
    /**
     * Create a new profiles instance.
     *
     * @param  string                          $apiKey
     * @param  \Beanstream\Http\Configuration  $config
     * @return void
     */
    public function __construct($apiKey, Configuration $config)
    {
        parent::__construct($apiKey, $config);
    }

    public function makePayment($data = [])
    {
        $url = $this->endpoints->getPaymentsUrl();
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function returnPayment($transID = '', $data = [])
    {
        $url = $this->endpoints->getPaymentsUrl($transID, 'returns');
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function voidTransaction($transID = '', $data = [])
    {
        $url = $this->endpoints->getPaymentsUrl($transID, 'void');
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function completePreAuth($transID = '', $data = [])
    {
        $url = $this->endpoints->getPaymentsUrl($transID, 'completions');
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function getPayment($transID = '')
    {
        $url = $this->endpoints->getPaymentsUrl($transID);
        $result = $this->connector->sendRequest($url, [], 'GET');
        return $result;
    }

    public function secureInteracOnlinePayment($merchantData = '')
    {
        $url = $this->endpoints->getPaymentsUrl($merchantData, 'continue');
        $result = $this->connector->sendRequest($url);
    }
}
