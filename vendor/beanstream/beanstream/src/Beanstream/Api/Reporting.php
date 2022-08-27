<?php

namespace Beanstream\Api;

use Beanstream\Http\Configuration;

/**
 * Handles generation of reports from Bambora
 *
 * @author Vincent Wilkie
 */
class Reporting extends Api
{
    /**
     * Create a new reporting instance.
     *
     * @param  string                          $apiKey
     * @param  \Beanstream\Http\Configuration  $config
     * @return void
     */
    public function __construct($apiKey, Configuration $config)
    {
        parent::__construct($apiKey, $config);
    }

    /**
     * Query for transactions using a date range and optional search criteria
     *
     * @param  array $searchQuery
     * @return mixed
     */
    public function searchQuery($searchCriteria = [])
    {
        $url = $this->endpoints->getReportingUrl();

        $result = $this->connector->sendRequest($url, $searchCriteria);

        return $result;
    }
}
