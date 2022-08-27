<?php

use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;
use application\modules\categories\models\Category;
use application\modules\classes\models\QBClass;

class syncserviceinqb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $QBBase;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_services');
        $this->CI->load->library('Common/QuickBooks/QBBase');

        $this->QBBase = $this->CI->qbbase;
        $this->settings =  $this->QBBase->settings;
        $this->dataService = $this->QBBase->dataService;
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
        if ($job) {
            $payload = unserialize($job->job_payload);
            $service = $this->CI->mdl_services->get($payload['id']);
            $classRef = null;
            $parentRef = null;
            if($service->is_bundle)
                return TRUE;
            $accountRef = getAccountRef($this->dataService);
            if($accountRef == 'refresh')
                return FALSE;
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
            if(!empty($service) && !empty($service->service_category_id)){
                $category = Category::where('category_id', $service->service_category_id)->first();
                if(!empty($category) && !empty($category->category_qb_id))
                    $parentRef = $category->category_qb_id;
            }
            if(!empty($service) && !empty($service->service_class_id)){
                $class = QBClass::where('class_id', $service->service_class_id)->first();
                if(!empty($class) && !empty($class->class_qb_id))
                    $classRef = $class->class_qb_id;
            }
            $serviceForQB = createServiceForQB($service, $accountRef, $parentRef, $classRef);
            if (!empty($service->service_qb_id) && $service->service_status == 0) {
                $theService = getQBEntityById('Item', $service->service_qb_id, $this->dataService);
                $updateService = Item::update($theService, ['Active' => false]);
                updateRecordInQBFromObject($updateService, $this->dataService, false, $service->service_id);
            } elseif (!$service->service_qb_id) {
                $checNameInQB = $this->CI->qbbase->findAll('Item', ["Name = '". $serviceForQB['Name'] ."'"]);
                if(!is_array($checNameInQB) && $checNameInQB == 'refresh')
                    return false;
                elseif(empty($checNameInQB)) {
                    $serviceObject = Item::create($serviceForQB);
                    $qbId = createRecordInQBFromObject($serviceObject, $this->dataService, false, false, $service->service_id);
                }else{
                    $qbId = !empty($checNameInQB[0]) && !empty($checNameInQB[0]->Id) ? $checNameInQB[0]->Id : 0;
                }

                if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbId){
                    $updateData = [
                        'service_qb_id' => 0
                    ];
                }else{
                    $updateData = [
                        'service_qb_id' => $qbId
                    ];
                }
                $this->CI->mdl_services->update($service->service_id, $updateData);
            } elseif ($service->service_qb_id) {
                $theItem = getQBEntityById('Item', $service->service_qb_id, $this->dataService);
                if (!$theItem)
                    return FALSE;
                unset($serviceForQB['IncomeAccountRef']);
                $updateService = Item::update($theItem, $serviceForQB);
                updateRecordInQBFromObject($updateService, $this->dataService, false, $service->service_id);
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
