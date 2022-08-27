<?php

namespace Beanstream\Http;

use Beanstream\Exceptions\ConfigurationException;

/**
 * Configuration class
 *
 * @author Vincent Wilkie
 */
class Configuration
{
    /**
     * Merchant ID
     *
     * @var string $merchantID
     */
    protected $merchantID;

    /**
     * API version
     *
     * @var string $apiVersion
     */
    protected $apiVersion = 'v1';

    /**
     * API platform
     *
     * @var string $platform
     */
    protected $platform = 'api';

    /**
     * Set the merchant ID.
     *
     * @param string $merchantID
     */
    public function setMerchantID($merchantID)
    {
        // Check to make sure merchant ID is 9 digits
        if (!preg_match('/^[0-9]{9}$/', $merchantID)) {
            throw new ConfigurationException('Invalid Merchant ID provided: "'.$merchantID.'" Expected 9 digits.');
        }

        $this->merchantID = $merchantID;
    }

    /**
     * Get the merchant ID.
     */
    public function getMerchantID()
    {
        return $this->merchantID;
    }

    /**
     * Set the API version.
     *
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Get the API version.
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Set the API platform.
     *
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        if (empty($platform)) {
            throw new ConfigurationException('Invalid platform provided: "'.$platform.'"');
        }

        $this->platform = $platform;
    }

    /**
     * Get the API platform.
     */
    public function getPlatform()
    {
        return $this->platform;
    }
}
