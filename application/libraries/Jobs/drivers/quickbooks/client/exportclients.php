<?php

use QuickBooksOnline\API\Facades\Customer;

class exportclients extends CI_Driver implements JobsInterface
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
            $payload = unserialize($job->job_payload);
            $i = $payload['count'];
            $clients = $this->CI->mdl_clients->find_all_with_limit([], 1000, $i, '', ['client_qb_id' => null]);
            if (!is_array($clients) || empty($clients)) {
                pushJob('quickbooks/item/exportservices', serialize(['module' => 'Item', 'count' => 0]));
                return TRUE;
            }
            foreach ($clients as $client) {
                $clientForQB = createClientForQB($client);
                $clientId = is_object($client) ? $client->client_id : $client['client_id'];
                if (!empty($clientForQB['DisplayName'])) {
                    $clientObject = Customer::create($clientForQB);
                    $qbId = createRecordInQBFromObject($clientObject, $this->dataService, false, false, $clientId);
                    if ($qbId == 'duplicate') {
                        $newClientForQB = addAddressToDisplayNameForQB($clientForQB);
                        $newClientObject = Customer::create($newClientForQB);
                        $qbId = createRecordInQBFromObject($newClientObject, $this->dataService, false, false, $clientId);
                    }
                    if ($qbId == 'duplicate') {
                        $newClientForQB = addAddressToDisplayNameForQB($clientForQB, $clientId);
                        $newClientObject = Customer::create($newClientForQB);
                        $qbId = createRecordInQBFromObject($newClientObject, $this->dataService, false, false, $clientId);
                    }
                    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                        return FALSE;
                    elseif (!$qbId) {
                        pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $clientId, 'qbId' => '']));
                        $i++;
                    } else {
                        $updateData = [
                            'client_qb_id' => $qbId
                        ];
                        $where = [
                            'client_id' => $clientId
                        ];
                        $this->CI->mdl_clients->update_client($updateData, $where);
                    }
                } else {
                    $i++;
                }
            }
            $clients = $this->CI->mdl_clients->find_all_with_limit([], 1000, $i, '', ['client_qb_id' => null]);
            if (is_array($clients) && !empty($clients))
                pushJob('quickbooks/client/exportclients', serialize(['module' => 'Customer', 'count' => $i]));
            else
                pushJob('quickbooks/item/exportservices', serialize(['module' => 'Item', 'count' => 0]));

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
