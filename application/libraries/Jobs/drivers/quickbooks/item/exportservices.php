<?php

use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;

class exportservices extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_services');

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
            $services = $this->CI->mdl_services->get_all_services([], 1000, $i, '', ['service_qb_id' => null]);
            $accountRef = getAccountRef($this->dataService);
            if($accountRef == 'refresh')
                return FALSE;
            if (!is_array($services) || empty($services)) {
                pushJob('quickbooks/invoice/exportinvoices', serialize(['module' => 'Invoice', 'count' => 0]));
                return TRUE;
            }
            if (!$accountRef) {
                $account = [
                    'Name' => 'Services',
                    'AccountType' => 'Income'
                ];
                $obj = Account::create($account);
                $accountRef = createRecordInQBFromObject($obj, $this->dataService);
            }
            if (!$accountRef)
                return FALSE;
            foreach ($services as $service) {
                $serviceForQB = createServiceForQB($service, $accountRef);
                $serviceObject = Item::create($serviceForQB);
                $qbId = createRecordInQBFromObject($serviceObject, $this->dataService,false, false, $service->service_id);
                if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbId) {
                    pushJob('quickbooks/item/synciteminqb', serialize(['id' => $service->service_id, 'qbId' => '']));
                    $i++;
                } else {
                    $updateData = [
                        'service_qb_id' => $qbId
                    ];
                    $this->CI->mdl_services->update($service->service_id, $updateData);
                }

            }
            $services = $this->CI->mdl_services->get_all_services([], 1000, $i, '', ['service_qb_id' => null]);
            if (is_array($services) && !empty($services))
                pushJob('quickbooks/item/exportservices', serialize(['module' => 'Item', 'count' => $i]));
            else
                pushJob('quickbooks/invoice/exportinvoices', serialize(['module' => 'Invoice', 'count' => 0]));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

}
