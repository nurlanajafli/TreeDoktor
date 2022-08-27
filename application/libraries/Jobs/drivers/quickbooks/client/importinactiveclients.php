<?php

use QuickBooksOnline\API\QueryFilter\QueryMessage;

class importinactiveclients extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
    }

    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if ($job) {
            $oneQuery = new QueryMessage();
            $oneQuery->sql = "SELECT";
            $oneQuery->entity = "Customer";
            $oneQuery->whereClause = ["Active = false"];
            $result = customQuery($oneQuery, $this->dataService);
            $error = checkError($this->dataService);
            if (!$error) {
                return FALSE;
            }
            $clientsToDB = getAllCustomerToDB($result, true);
            if (!$result || !is_array($clientsToDB) || empty($clientsToDB)) {
                pushJob('quickbooks/client/importclients', serialize(['module' => 'All']));
                return TRUE;
            }
            $clientsContacts = getAllClientsContactsToDB($result);
            foreach ($clientsToDB as $client) {
                $clientId = $this->CI->mdl_clients->add_new_client_with_data($client);
                $clientsContacts = addClientIdToClientsContacts($clientsContacts, $client['client_name'], $clientId);
            }
            foreach ($clientsContacts as $clientContact) {
                $this->CI->mdl_clients->add_client_contact($clientContact);
            }
            pushJob('quickbooks/client/importclients', serialize(['module' => 'All']));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
