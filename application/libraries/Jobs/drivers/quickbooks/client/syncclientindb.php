<?php
use application\modules\clients\models\ClientsContact;
class syncclientindb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $QBAttachmentActions;
    private $action;
    private $route;
    private $itemId;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->library('Common/QuickBooks/QBAttachmentActions');
        $this->CI->load->library('Common/QuickBooks/QBClientActions');
        $this->CI->load->library('Common/ClientsActions');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
        $this->route = 'pull';
    }

    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if(!$this->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $customer = findByIdInQB($payload['module'], $payload['qbId'], $this->dataService);
            if (!$customer) {
                $message = 'Error retrieving data from QuickBooks (qbId = ' . $payload['qbId'] . ')';
                createQBLog('customer', 'get', 'pull', -1, $message);
                return FALSE;
            }
//            if(is_object($customer) && !empty($customer->ParentRef)){
//                $clientQbId = $this->CI->qbclientactions->getCustomerParentQbId($customer->ParentRef);
//                if($clientQbId == 'refresh')
//                    return FALSE;
//                $clientId = getClientId($clientQbId);
//                if(empty($clientId))
//                    return FALSE;
//                $ccFromDb = ClientsContact::where('cc_qb_id', $customer->Id)->first();
//                $ccForDb = $this->CI->clientsactions->getCCFromQbCustomerToDB($customer, $clientId);
//                if ($customer->Active == 'false'){
//                    ClientsContact::where('cc_qb_id', $customer->Id)->delete();
//                    $message = getCcMessage('Delete', $ccForDb, $ccFromDb);
//                } elseif(!empty($ccFromDb)){
//                    ClientsContact::where('cc_qb_id', $customer->Id)->update($ccForDb);
//                    $message = getCcMessage('Update', $ccForDb, $ccFromDb);
//                } else{
//                    ClientsContact::create($ccForDb);
//                    $message = getCcMessage('Create', $ccForDb);
//                }
//                if ($message)
//                    make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
//                return TRUE;
//            }
            $this->QBAttachmentActions = new $this->CI->qbattachmentactions('Customer', $payload['qbId']);
            $attachment = $this->QBAttachmentActions->get();
            if (isset($attachment->client_lng) && isset($attachment->client_lat)) {
                $customer->client_lng = $attachment->client_lng;
                $customer->client_lat = $attachment->client_lat;
            }
            $customerArr[] = $customer;
            $clientsToDB = getAllCustomerToDB($customerArr);
            $clientsContacts = getAllClientsContactsToDB($customerArr);
            $operation = $payload['operation'];
            $client = $clientsToDB[0];
            $message = '';
            $clientFromDB = $this->CI->mdl_clients->get_clients('*', ['client_qb_id' => $client['client_qb_id']])->row();
            if (!$clientFromDB) {
                if($operation == 'Update' && $customer->Active == 'false')
                    return TRUE;
                $this->action = 'create';
                $this->itemId = $this->CI->mdl_clients->add_new_client_with_data($client);
                $message = 'QuickBooks: Hey, I just created a new client.';
                $clientsContacts = addClientIdToClientsContacts($clientsContacts, $client['client_name'], $this->itemId);
            } elseif ($clientFromDB) {
                $this->action = 'update';
                if($clientFromDB->client_type == 3)
                    unset($client['client_type']);
                $result = $this->CI->mdl_clients->update_client($client, ['client_qb_id' => $client['client_qb_id']]);
                if (!$result)
                    return FALSE;
                $message = getClientMessage($clientFromDB, $client);
                $this->itemId = $clientFromDB->client_id;
                $clientsContacts = addClientIdToClientsContacts($clientsContacts, $client['client_name'], $this->itemId);
            } elseif ($operation == 'Delete') {
                $this->action = 'delete';
                $clientFromDB = $this->CI->mdl_clients->get_clients('client_id', ['client_qb_id' => $client['client_qb_id']])->row();
                $this->itemId = $clientFromDB->client_id;
                $this->CI->mdl_clients->complete_client_removal($clientFromDB->client_id);
            }
            if ($message)
                make_notes($this->itemId, $message, $type = 'system', $lead_id = NULL);

//            if (isset($attachment->cc) && isset($attachment->ccMd5)) {
//                $cc = $this->CI->mdl_clients->get_client_contacts('cc_client_id = ' . $this->itemId);
//                if (md5(json_encode($cc)) != $attachment->ccMd5)
//                    $this->actionsWithClientsContacts($attachment->cc);
//            }
            $this->actionsWithClientsContacts($clientsContacts);
            createQBLog('customer', $this->action, $this->route, $this->itemId);
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    public function actionsWithClientsContacts($clientsContacts)
    {
        foreach ($clientsContacts as $clientContact) {
            $message = '';
            $where = [];
            if (is_object($clientContact)) {
                $clientContact = (array)$clientContact;
                $where = [
                    'cc_id' => $clientContact['cc_id']
                ];
                unset($clientContact['cc_id']);
                $clientId = $clientContact['cc_client_id'];
            }
            else{
                $clientId = $clientContact['cc_client_id'];
                $where = [
                    'cc_client_id' => $clientId,
                    'cc_print' => $clientContact['cc_print']
                ];
            }
            $cc = $this->CI->mdl_clients->get_client_contact($where);
            if (is_array($cc) && !empty($cc)) {
                unset($clientContact['cc_title']);
                unset($clientContact['cc_name']);
                unset($clientContact['cc_phone_clean']);
                $this->CI->mdl_clients->update_client_contact($clientContact, $where);
                $message = getCcMessage('Update', $clientContact, $cc);
            } else {
                $this->CI->mdl_clients->add_client_contact($clientContact);
                $message = getCcMessage('Create', $clientContact);
            }
            if ($message)
                make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
        }
    }
}
