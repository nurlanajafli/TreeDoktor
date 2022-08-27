<?php

use QuickBooksOnline\API\Facades\Customer;

class exportclientsv3 extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->library('Common/QuickBooks/QBAttachmentActions');

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
            $module = $payload['module'];
            $clients = $this->CI->mdl_clients->find_all_with_limit([], 900, 0, '', ['client_qb_id' => null]);
            if ((!is_array($clients) || empty($clients)) && $module == 'All') {
                pushJob('quickbooks/item/exportservicesv3', serialize(['module' => $module, 'count' => 0]));
                return TRUE;
            }
            $batch = $this->dataService->CreateNewBatch();
            $sql = 'SELECT client_id FROM clients WHERE client_qb_id is null ORDER BY client_id DESC LIMIT 1';
            $maxClientId = $this->CI->db->query($sql)->result();
            $i = 0;
            $arrClients = [];
            foreach ($clients as $client) {
                $clientForQB = createClientForQB($client);
                $clientId = is_object($client) ? $client->client_id : $client['client_id'];
                if (!empty($clientForQB['DisplayName'])) {
                    $duplicate = $this->CI->mdl_clients->get_clients('client_id', ['client_name' => $clientForQB['DisplayName']])->result();
                    if(is_array($duplicate) && !empty($duplicate) && count($duplicate) > 1)//countOk
                        $clientForQB = addAddressToDisplayNameForQB($clientForQB);
                    $arrClients[] = $clientId;
                    $i++;
                    $clientObject = Customer::create($clientForQB);
                    $batch->AddEntity($clientObject, $clientId, "create");
                    if ($i == 30 || $maxClientId[0]->client_id == $clientId) {
                        $i = 0;
                        $batch->Execute();
                        $resultError = checkBatchError($batch, $this->dataService);
                        if (!$resultError)
                            return FALSE;
                        foreach ($arrClients as $id) {
                            $batchItemResponse = $batch->intuitBatchItemResponses[$id];
                            if (isset($batchItemResponse->entity->Id)) {
                                $qbId = $batchItemResponse->entity->Id;
                                $updateData = [
                                    'client_qb_id' => $qbId
                                ];
                                $where = [
                                    'client_id' => $id
                                ];
                                $this->CI->mdl_clients->update_client($updateData, $where);
                                // create attachment
                                $dataForAttachmentQB = getDataForCustomerAttachmentInQB($id);
                                $QBAttachmentActions = new $this->CI->qbattachmentactions('Customer', $qbId);
                                $QBAttachmentActions->create($dataForAttachmentQB);
                            } elseif (isset($batchItemResponse->exception)) {
                                pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $id, 'qbId' => '']));
                            }
                        }
                        if($maxClientId[0]->client_id == $clientId && $module == 'All'){
                            pushJob('quickbooks/item/exportservicesv3', serialize(['module' => $module, 'count' => 0]));
                            deleteLogsInTmp();
                            return TRUE;
                        }
                        $arrClients = [];
                        $batch = $this->dataService->CreateNewBatch();
                    }
                }else{
                    $updateData = [
                        'client_qb_id' => 0
                    ];
                    $where = [
                        'client_id' => $clientId
                    ];
                    $this->CI->mdl_clients->update_client($updateData, $where);
                }
            }
            $clients = $this->CI->mdl_clients->find_all_with_limit([], 100, 0, '', ['client_qb_id' => null]);
            if (is_array($clients) && !empty($clients))
                pushJob('quickbooks/client/exportclientsv3', serialize(['module' => $module, 'count' => 0]));
            elseif($module == 'All')
                pushJob('quickbooks/item/exportservicesv3', serialize(['module' => $module, 'count' => 0]));

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
