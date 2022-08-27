<?php

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\QueryFilter\QueryMessage;

class QBBase
{
    public $dataService;
    public $settings;
    protected $CI;
    protected $module;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('mdl_settings_orm');
        $this->CI->mdl_settings_orm->install();

        $this->settings = $this->getQbSettings();

        if(!$this->settings || empty($this->settings))
            return;

        if ($this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = $this->dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID']);
    }

    public function checkAccessToken()
    {
        if (empty($this->settings['accessToken']))
            return FALSE;
        return TRUE;
    }

    public function get($id)
    {
        $record = $this->dataService->FindById($this->module, $id);
        $error = $this->checkError();
        if (!$error)
            return $record;
        return FALSE;
    }

    public function checkError()
    {
        $error = $this->dataService->getLastError();
        if ($error) {
            $statusCode = $error->getHttpStatusCode();
            if ($statusCode == 401) {
                $this->refreshToken();
            }
            return TRUE;
        }
        return FALSE;
    }

    public function refreshToken()
    {
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessToken = $OAuth2LoginHelper->refreshToken();
        $this->createOrUpdateQbAccessToken($accessToken);
        $error = $OAuth2LoginHelper->getLastError();
        if ($error)
            return FALSE;
        $this->dataService->updateOAuth2Token($accessToken);
    }

    public function createOrUpdateQbAccessToken($accessToken)
    {
        if (is_object($accessToken)) {
            $settings = [
                'clientID' => $accessToken->getClientId(),
                'clientSecret' => $accessToken->getClientSecret(),
                'accessTokenKey' => $accessToken->getaccessToken(),
                'refreshTokenKey' => $accessToken->getrefreshToken(),
                'QBORealmID' => $accessToken->getRealmID(),
                'baseUrl' => config_item('qb_mode') && array_search(config_item('qb_mode'), ['development', 'production']) !== FALSE ? config_item('qb_mode') : 'production',
                'accessToken' => serialize($accessToken)
            ];
            foreach ($settings as $key => $value) {
                $isHidden = ($key != 'clientID' && $key != 'clientSecret') ? true : false;
                $data = [
                    'stt_key_name' => $key,
                    'stt_key_value' => $value,
                    'stt_is_hidden' => $isHidden
                ];
                $setting = $this->CI->mdl_settings_orm->get_by('stt_key_name', $key);
                if (is_object($setting)) {
                    $this->CI->mdl_settings_orm->update_by('stt_key_name', $key, $data);
                } else {
                    $this->CI->mdl_settings_orm->insert($data, true);
                }
            }
        }
    }

    function getAll($module = null)
    {
        $data = [];
        $i = 1;
        $qbModule = $module ?: $this->module;
        while (true) {
            $allData = $this->dataService->FindAll($qbModule, $i, 500);
            $error = $this->checkError();
            if ($error)
                return 'error';
            if (!$allData || empty($allData)) {
                break;
            }
            foreach ($allData as $oneRecord) {
                $i++;
                array_push($data, $oneRecord);
            }
        }
        return $data;
    }

    private function getQbSettings()
    {
        $clientId = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'ClientID');
        $clientSecret = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'ClientSecret');
        $accessToken = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey');
        $refreshToken = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'refreshTokenKey');
        $realmId = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'QBORealmID');
        $baseUrl = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'baseUrl');
        $taxRate = config_item('tax');
        $prefix = config_item('prefix');
        $location = explode(',', config_item('Location'))[0];
        $accessTokenFull = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'accessToken');
        $authorizationRequestUrl = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'AuthorizationRequestUrl');
        $tokenEndPointUrl = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'TokenEndPointUrl');
        $oauthScope = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'OauthScope');
        $oauthRedirectUri = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'OauthRedirectUri');
        $interest = $this->CI->mdl_settings_orm->get_by('stt_key_name', 'interest');
        $us = config_item('office_country') == 'United States of America';
        $sync = json_decode(config_item('synchronization'), TRUE);
        $stateInQB = isset($sync['in']['state']) ? $sync['in']['state'] : 1;
        $stateFromQB = isset($sync['from']['state']) ? $sync['from']['state'] : 1;
        $stateSyncInvoiceNO = config_item('syncInvoiceNO') == 'Disable' ? 1 : 0;
        if (!$clientId || (isset($clientId->stt_key_value) && !$clientId->stt_key_value))
            return FALSE;
        $interestValue = null;
        if ($interest)
            $interestValue = $interest->stt_key_value;
        $settings = [
            $clientId->stt_key_name => $clientId->stt_key_value,
            $clientSecret->stt_key_name => $clientSecret->stt_key_value,
            $accessToken->stt_key_name => $accessToken->stt_key_value,
            $refreshToken->stt_key_name => $refreshToken->stt_key_value,
            $realmId->stt_key_name => $realmId->stt_key_value,
            $baseUrl->stt_key_name => $baseUrl->stt_key_value,
            $accessTokenFull->stt_key_name => $accessTokenFull->stt_key_value,
            $authorizationRequestUrl->stt_key_name => $authorizationRequestUrl->stt_key_value,
            $tokenEndPointUrl->stt_key_name => $tokenEndPointUrl->stt_key_value,
            $oauthScope->stt_key_name => $oauthScope->stt_key_value,
            $oauthRedirectUri->stt_key_name => $oauthRedirectUri->stt_key_value,
            'tax_rate' => $taxRate,
            'interest' => $interestValue,
            'prefix' => $prefix,
            'location' => $location,
            'us' => $us,
            'stateInQB' => $stateInQB,
            'stateFromQB' => $stateFromQB,
            'stateSyncInvoiceNO' => $stateSyncInvoiceNO
        ];
        return $settings;
    }

    public function customQuery(QueryMessage $message)
    {
        // Run a query
        $queryString = $message->getString();
        $entities = $this->dataService->Query($queryString);
        $error = $this->checkError();
        if ($error)
            return 'refresh';
        return $entities;
    }

    private function dataServiceConfigureFromArguments($clientId, $clientSecret, $accessToken, $refreshToken, $realmId)
    {
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $clientId,
            'ClientSecret' => $clientSecret,
            'accessTokenKey' => $accessToken,
            'refreshTokenKey' => $refreshToken,
            'QBORealmID' => $realmId,
            'baseUrl' => config_item('qb_mode') && array_search(config_item('qb_mode'), ['development', 'production']) !== FALSE ? config_item('qb_mode') : 'production'
        ));
        return $dataService;
    }

    public function getAllByIterator($iterator)
    {
        $allData = $this->dataService->FindAll($this->module, $iterator, 500);
        $error = $this->checkError();
        if ($error)
            return FALSE;
        return $allData;
    }

    public function findAll(string $module, array $where = []){
        // Build a query
        $oneQuery = new QueryMessage();
        $oneQuery->sql = "SELECT";
        $oneQuery->entity = $module;
        if(!empty($where))
            $oneQuery->whereClause = $where;

        return $this->customQuery($oneQuery);
    }
}