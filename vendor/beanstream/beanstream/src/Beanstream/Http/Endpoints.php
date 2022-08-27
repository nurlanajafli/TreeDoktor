<?php

namespace Beanstream\Http;

/**
 * Endpoints class for handling API endpoints for Bambora/Beanstream
 *
 * @author Vincent Wilkie
 */
class Endpoints
{
    /**
     * Beanstream endpoint (legacy)
     */
    const BASE_URL_BEANSTREAM = '%s.beanstream.com';

    /**
     * Bambora endpoint
     */
    const BASE_URL_BAMBORANA = '%s.na.bambora.com';

    /**
     * The base url endpoint for all requests.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The API version to use for the base url endpoints.
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * The API platform to determine new vs legacy.
     *
     * @var string
     */
    protected $platform;

    /**
     * Create a new endpoints instance.
     *
     * @param string $apiVersion
     * @param string $platform
     */
    public function __construct($apiVersion, $platform)
    {
        $this->apiVersion = $apiVersion;
        $this->platform = $platform;

        $format = ($this->platform === 'www' ? self::BASE_URL_BEANSTREAM.'/api' : self::BASE_URL_BAMBORANA);

        $this->baseUrl = 'https://'.sprintf($format, $this->platform).'/'.$this->apiVersion;
    }

    /**
     * Get the payments endpoint.
     *
     * @return string
     */
    public function getPaymentsUrl($transID = '', $suffix = '')
    {
        $format = $this->baseUrl.'/payments%s%s';

        if (!empty($transID)) {
            $transID = '/'.$transID;
        }

        if (!empty($suffix)) {
            if (!in_array($suffix, ['returns', 'void', 'completions', 'continue'])) {
                throw new ConnectorException('Invalid payment endpoint');
            }

            $suffix = '/'.$suffix;
        }

        return sprintf($format, $transID, $suffix);
    }

    /**
     * Get the reporting endpoint.
     *
     * @return string
     */
    public function getReportingUrl()
    {
        return $this->baseUrl.'/reports';
    }

    /**
     * Get the profiles endpoint.
     *
     * @param  string $profileID The payment profile ID, also known as the Customer Code.
     * @return string
     */
    public function getProfilesUrl($profileID = '')
    {
        $format = $this->baseUrl.'/profiles%s';

        if (!empty($profileID)) {
            $profileID = '/'.$profileID;
        }

        return sprintf($format, $profileID);
    }

    /**
     * Get the profiles cards endpoint.
     *
     * @param  string $profileID The payment profile ID, also known as the Customer Code.
     * @param  string $cardID    The card ID of a particular card
     * @return string
     */
    public function getProfilesCardsUrl($profileID = '', $cardID = '')
    {
        $format = $this->getProfilesUrl($profileID).'/cards%s';

        if (!empty($cardID)) {
            $cardID = '/'.$cardID;
        }

        return sprintf($format, $cardID);
    }

    /**
     * Get the tokenization endpoint.
     *
     * @return string
     */
    public function getTokenizationUrl()
    {
        $baseUrl = 'https://'.sprintf(($this->platform === 'www' ? self::BASE_URL_BEANSTREAM : self::BASE_URL_BAMBORANA), $this->platform);

        return $baseUrl.'/scripts/tokenization/tokens';
    }
}
