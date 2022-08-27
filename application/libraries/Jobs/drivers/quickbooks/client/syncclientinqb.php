<?php

use QuickBooksOnline\API\Facades\Customer;

class syncclientinqb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $QBAttachmentActions;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->library('Common/QuickBooks/QBAttachmentActions');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
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
        if(!$this->settings['stateInQB'])
            die;
        elseif ($job) {
            $payload = unserialize($job->job_payload);
            $client = $this->CI->mdl_clients->get_client_by_id($payload['id']);
            if ($client) {
                $clientId = is_object($client) ? $client->client_id : $client['client_id'];
                $dataForAttachmentQB = getDataForCustomerAttachmentInQB($clientId);
            }

            if (!is_object($client) && empty($client) && $payload['qbId']) {
                $theCustomer = getQBEntityById('Customer', $payload['qbId'], $this->dataService);
                if (!$theCustomer)
                    return FALSE;
                $updateCustomer = Customer::update($theCustomer, ['Active' => false]);
                $result = updateRecordInQBFromObject($updateCustomer, $this->dataService, false, $payload['id']);
                if (!$result)
                    return FALSE;
            } elseif (!$client->client_qb_id) {
                $where = [
                    'client_id' => $clientId
                ];
                $updateData = [
                    'client_qb_id' => 0
                ];
                $this->CI->mdl_clients->update_client($updateData, $where);
                $customerId = createClientInQB($client, $this->dataService);
                if (!$customerId)
                    return FALSE;
                $this->QBAttachmentActions = new $this->CI->qbattachmentactions('Customer', $customerId);
                $this->actionsWithAttachment('create', $dataForAttachmentQB);
            } elseif ($client->client_qb_id) {
                $clientForQB = createClientForQB($client);
                $theCustomer = getQBEntityById('Customer', $client->client_qb_id, $this->dataService);
                if (!$theCustomer)
                    return FALSE;
                $updateCustomer = Customer::update($theCustomer, $clientForQB);
                $result = updateRecordInQBFromObject($updateCustomer, $this->dataService, false, $client->client_id);
                if (!$result)
                    return FALSE;
                $this->QBAttachmentActions = new $this->CI->qbattachmentactions('Customer', $client->client_qb_id);
                $this->actionsWithAttachment('update', $dataForAttachmentQB);
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    private function actionsWithAttachment($operation, $data)
    {
        if ($operation == 'create') {
            $this->QBAttachmentActions->create($data);
        } elseif ($operation == 'update') {
            $this->QBAttachmentActions->get();
            $this->QBAttachmentActions->delete();
            $this->QBAttachmentActions->create($data);
        }
    }
}
