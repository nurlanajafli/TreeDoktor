<?php

use QuickBooksOnline\API\Facades\Customer;

class exportclientsv2 extends CI_Driver implements JobsInterface
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
            while ($this->CI->db->where('client_qb_id IS NULL')->count_all_results('clients')) {
                $sql = 'START TRANSACTION';
                $this->CI->db->query($sql);
                $sql = 'SELECT * FROM clients WHERE client_qb_id IS NULL ORDER BY client_id ASC LIMIT 100 FOR UPDATE';
                $clients = $this->CI->db->query($sql)->result();
                if ($clients) {
                    foreach ($clients as $client) {
                        $updateData = [
                            'client_qb_id' => -1
                        ];
                        $where = [
                            'client_id' => $client->client_id
                        ];
                        $this->CI->mdl_clients->update_client($updateData, $where);
                    }
                }
                $sql = 'COMMIT';
                $this->CI->db->query($sql);
                if ($clients) {
                    $clientId = '';
                    foreach ($clients as $client) {
                        $clientForQB = createClientForQB($client);
                        $clientId = is_object($client) ? $client->client_id : $client['client_id'];

                        if (!empty($clientForQB['DisplayName'])) {
                            $clientObject = Customer::create($clientForQB);
                            $where = [
                                'client_id' => $clientId
                            ];
                            $qbId = createRecordInQBFromObject($clientObject, $this->dataService,false,false, $clientId);

                            if ($qbId == 'duplicate') {
                                $newClientForQB = addAddressToDisplayNameForQB($clientForQB);
                                $newClientObject = Customer::create($newClientForQB);
                                $qbId = createRecordInQBFromObject($newClientObject, $this->dataService,false,false, $clientId);
                            }
                            if ($qbId == 'duplicate') {
                                $newClientForQB = addAddressToDisplayNameForQB($clientForQB, $clientId);
                                $newClientObject = Customer::create($newClientForQB);
                                $qbId = createRecordInQBFromObject($newClientObject, $this->dataService,false,false, $clientId);
                            }
                            if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                                return FALSE;
                            elseif (!$qbId) {
                                $updateData = [
                                    'client_qb_id' => null
                                ];
                                $this->CI->mdl_clients->update_client($updateData, $where);
                                pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $clientId, 'qbId' => '']));
                            } else {
                                $updateData = [
                                    'client_qb_id' => $qbId
                                ];

                                $this->CI->mdl_clients->update_client($updateData, $where);
                            }
                        }
                    }
                    $sql = 'SELECT client_id FROM clients ORDER BY client_id DESC LIMIT 1';
                    $clients = $this->CI->db->query($sql)->result();
                    if($clients[0]->client_id == $clientId){
                        pushJob('quickbooks/item/exportservicesv2', serialize(['module' => 'Item', 'count' => 0]));
                        break;
                    }
                }
            }

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
