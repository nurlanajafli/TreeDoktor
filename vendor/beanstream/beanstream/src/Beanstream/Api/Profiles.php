<?php

namespace Beanstream\Api;

use Beanstream\Http\Configuration;

/**
 * Interaction with payment profiles from Bambora
 *
 * @author Vincent Wilkie
 */
class Profiles extends Api
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

    public function createProfile($data = [])
    {
        $url = $this->endpoints->getProfilesUrl();
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function getProfile($profileID = '')
    {
        $url = $this->endpoints->getProfilesUrl($profileID);
        $result = $this->connector->sendRequest($url, [], 'GET');
        return $result;
    }

    public function updateProfile($profileID = '', $data = [])
    {
        $url = $this->endpoints->getProfilesUrl($profileID);
        $result = $this->connector->sendRequest($url, $data, 'PUT');
        return $result;
    }

    public function deleteProfile($profileID = '')
    {
        $url = $this->endpoints->getProfilesUrl($profileID);
        $result = $this->connector->sendRequest($url, [], 'DELETE');
        return $result;
    }

    public function getCards($profileID = '')
    {
        $url = $this->endpoints->getProfilesCardsUrl($profileID);
        $result = $this->connector->sendRequest($url, [], 'GET');
        return $result;
    }

    public function addCard($profileID = '', $data = [])
    {
        $url = $this->endpoints->getProfilesCardsUrl($profileID);
        $result = $this->connector->sendRequest($url, $data);
        return $result;
    }

    public function getCard($profileID = '', $cardID = '')
    {
        $url = $this->endpoints->getProfilesCardsUrl($profileID, $cardID);
        $result = $this->connector->sendRequest($url, [], 'GET');
        return $result;
    }

    public function updateCard($profileID = '', $cardID = '', $data = [])
    {
        $url = $this->endpoints->getProfilesCardsUrl($profileID, $cardID);
        $result = $this->connector->sendRequest($url, $data, 'PUT');
        return $result;
    }

    public function deleteCard($profileID = '', $cardID = '')
    {
        $url = $this->endpoints->getProfilesCardsUrl($profileID, $cardID);
        $result = $this->connector->sendRequest($url, [], 'DELETE');
        return $result;
    }
}
