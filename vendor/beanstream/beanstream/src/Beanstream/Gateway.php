<?php

namespace Beanstream;

use Exception;
use Beanstream\Http\Configuration;
use Beanstream\Api\Payments;
use Beanstream\Api\Profiles;
use Beanstream\Api\Reporting;
use Beanstream\Api\Tokenization;

/**
 * Gateway class
 *
 * @author Vincent Wilkie
 */
class Gateway
{
    /**
     * The payments implementation.
     *
     * @var \Beanstream\Api\Payments
     */
    public $payments = null;

    /**
     * The reporting implementation.
     *
     * @var \Beanstream\Api\Reporting
     */
    public $reporting = null;

    /**
     * The profiles implementation.
     *
     * @var \Beanstream\Api\Profiles
     */
    public $profiles = null;

    /**
     * The tokenization implementation.
     *
     * @var \Beanstream\Api\Tokenization
     */
    public $tokenization = null;

    /**
     * Create a new gateway instance.
     *
     * @param array $config
     * @return void
     */
    public function __construct($config = [])
    {
        try {
            $merchantID = $config['merchantID'] ?? '';
            $apiVersion = $config['apiVersion'] ?? 'v1';
            $platform = $config['platform'] ?? 'api';

            $apiKeys = $config['apiKeys'] ?? [];
            $paymentsKey = $apiKeys['payments'] ?? '';
            $reportingKey = $apiKeys['reporting'] ?? '';
            $profilesKey = $apiKeys['profiles'] ?? '';

            $configuration = new Configuration();
            $configuration->setMerchantID($merchantID);
            $configuration->setApiVersion($apiVersion);
            $configuration->setPlatform($platform);

            $this->payments = new Payments($paymentsKey, $configuration);
            $this->reporting = new Reporting($reportingKey, $configuration);
            $this->profiles = new Profiles($profilesKey, $configuration);
            $this->tokenization = new Tokenization($paymentsKey, $configuration);
        } catch (Exception $e) {
            throw new Exception(get_class($e).': '.$e->getMessage);
        }
    }
}
