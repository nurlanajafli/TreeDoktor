<?php

use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;
use application\modules\categories\models\Category;

class exportservicesv3 extends CI_Driver implements JobsInterface
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
            $module = $payload['module'];
            $services = $this->CI->mdl_services->get_all_services([], 900, 0, '', ['service_qb_id' => null, 'is_bundle' => 0]);
            $accountRef = getAccountRef($this->dataService);
            if($accountRef == 'refresh')
                return FALSE;
            if ((!is_array($services) || empty($services)) && $module == 'All') {
                pushJob('quickbooks/invoice/exportinvoicesv3', serialize(['module' => $module, 'count' => 0]));
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

            $batch = $this->dataService->CreateNewBatch();
            $sql = 'SELECT service_id FROM services WHERE service_qb_id is null AND is_bundle = 0 ORDER BY service_id DESC LIMIT 1';
            $maxServiceId = $this->CI->db->query($sql)->result();
            if(empty($maxServiceId))
                return $this->checkServices($module);
            $i = 0;
            $arrServices = [];
            foreach ($services as $service) {
                $classRef = null;
                if(!empty($service) && !empty($service->service_category_id)){
                    $class = Category::where('category_id', $service->service_category_id)->first();
                    if(!empty($class) && !empty($class->category_qb_id))
                        $classRef = $class->category_qb_id;
                }
                $serviceForQB = createServiceForQB($service, $accountRef, $classRef);
                $serviceObject = Item::create($serviceForQB);
                $serviceId = $service->service_id;
                $arrServices[] = $serviceId;
                $i++;
                $batch->AddEntity($serviceObject, $serviceId, "create");
                if ($i == 30 || $maxServiceId[0]->service_id == $serviceId) {
                    $i = 0;
                    $batch->Execute();
                    $resultError = checkBatchError($batch, $this->dataService);
                    if (!$resultError)
                        return FALSE;
                    foreach ($arrServices as $id) {
                        $batchItemResponse = $batch->intuitBatchItemResponses[$id];
                        if (isset($batchItemResponse->entity->Id) && !empty($batchItemResponse->entity->Id)) {
                            $qbId = $batchItemResponse->entity->Id;
                            $updateData = [
                                'service_qb_id' => $qbId
                            ];
                            $this->CI->mdl_services->update($id, $updateData);
                        } elseif (isset($batchItemResponse->exception)) {
                            pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $id, 'qbId' => '']));
                        }
                    }
                    if ($maxServiceId[0]->service_id == $serviceId && $module == 'All') {
                        pushJob('quickbooks/invoice/exportinvoicesv3', serialize(['module' => $module, 'count' => 0]));
                        deleteLogsInTmp();
                        return TRUE;
                    }
                    $arrServices = [];
                    $batch = $this->dataService->CreateNewBatch();
                }
            }
            return $this->checkServices($module);
        }
        return FALSE;
    }

    private function checkServices($module){
        $services = $this->CI->mdl_services->get_all_services([], 30, 0, '', ['service_qb_id' => null, 'is_bundle' => 0]);
        if ($services && !empty($services))
            pushJob('quickbooks/item/exportservicesv3', serialize(['module' => $module, 'count' => 0]));
        elseif($module == 'All')
            pushJob('quickbooks/invoice/exportinvoicesv3', serialize(['module' => $module, 'count' => 0]));
        deleteLogsInTmp();
        return TRUE;
    }

}
