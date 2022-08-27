<?php

use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;

class syncservicesfromqb extends CI_Driver implements JobsInterface
{
    private $CI;
    private $qbServiceActions;
    private $dbServiceActions;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->library('Common/ServicesActions');
        $this->CI->load->library('Common/QuickBooks/QBServiceActions');
        $this->qbServiceActions = $this->CI->qbserviceactions;
        $this->dbServiceActions = $this->CI->servicesactions;
    }

    public function getPayload($data = NULL)
    {
        if (!$data || !$this->qbServiceActions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if ($job) {
            $payload = unserialize($job->job_payload);
            $servicesFromQB = $this->qbServiceActions->getAll();
            if ($servicesFromQB == 'error') {
                return FALSE;
            }
            elseif (empty($servicesFromQB)){
                $this->checkModule($payload);
                return TRUE;
            }
            foreach ($servicesFromQB as $service) {
                $result = $this->dbServiceActions->setServiceByName($service->Name);
                if ($result && !$this->dbServiceActions->getQBid()) {
                    $this->dbServiceActions->setQBid($service->Id);
                    $this->dbServiceActions->update();
                }
            }
            $this->checkModule($payload);
            return TRUE;
        }
        return FALSE;
    }
    private function checkModule($payload){
        if(isset($payload['module']))
            pushJob('quickbooks/item/exportservicesv3', serialize(['module' => 'Item']));
    }
}