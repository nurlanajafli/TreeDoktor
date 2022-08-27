<?php
use application\modules\clients\models\ClientsContact;

class importclients extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->library('Common/ClientsActions');
        $this->CI->load->library('Common/QuickBooks/QBClientActions');

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
            $i = 1;
            $checkErr = false;
            while (true) {
                $clients = $this->dataService->FindAll('Customer', $i, 500);
                $error = checkError($this->dataService);
                if (!$error) {
                    $checkErr = true;
                    break;
                }
                if(is_array($clients) && !empty($clients))
                    $i += count($clients);//countOk
                elseif (is_object($clients) && !empty($clients))
                    $i += 1;
                $clientsToDB = getAllCustomerToDB($clients, true);
                if (!$clients) {
                    break;
                }
                if (!is_array($clientsToDB) || empty($clientsToDB)) {
                    continue;
                }
                $clientsContacts = getAllClientsContactsToDB($clients);
                foreach ($clientsToDB as $client) {
                    $clientId = $this->CI->mdl_clients->add_new_client_with_data($client);
                    $clientsContacts = addClientIdToClientsContacts($clientsContacts, $client['client_name'], $clientId);
                    createQBLog('customer', 'create', 'pull', $clientId);
                }
                foreach ($clientsContacts as $clientContact) {
                    $this->CI->mdl_clients->add_client_contact($clientContact);
                }

                // if child
//                foreach ($clients as $customer){
//                    if(is_object($customer) && !empty($customer->ParentRef)){
//                        $clientQbId = $this->CI->qbclientactions->getCustomerParentQbId($customer->ParentRef);
//                        if($clientQbId == 'refresh')
//                            continue;
//                        $clientId = getClientId($clientQbId);
//                        if(empty($clientId))
//                            continue;
//                        $ccFromDb = ClientsContact::where('cc_qb_id', $customer->Id)->first();
//                        $ccForDb = $this->CI->clientsactions->getCCFromQbCustomerToDB($customer, $clientId);
//                        if(!empty($ccFromDb)){
//                            ClientsContact::where('cc_qb_id', $customer->Id)->update($ccForDb);
//                            $message = getCcMessage('Update', $ccForDb, $ccFromDb);
//                        } else{
//                            ClientsContact::create($ccForDb);
//                            $message = getCcMessage('Create', $ccForDb);
//                        }
//                        if ($message)
//                            make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
//                    }
//                }
            }
            if ($checkErr)
                return FALSE;
            if ($module == 'All')
                pushJob('quickbooks/class/importclasses', serialize(['module' => 'All']));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
