<?php
require_once('QBBase.php');

class QBEstimateActions extends QBBase
{
    protected $module = 'Estimate';
    protected $estimate;
    public $dbId;
    private $statuses = ['Pending', 'Accepted'];
    private $statusesToDB = [
        'Pending' => 3,
        'Accepted' => 6
    ];

    public function __construct($estimateQBid = null)
    {
        parent::__construct();
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_services');
        if ($estimateQBid) {
            $this->estimate = $this->get($estimateQBid);
            if (!$this->estimate)
                return FALSE;
        }
    }

    public function setDBid($id)
    {
        $this->dbId = $id;
    }

    public function set($estimate)
    {
        $this->estimate = $estimate;
    }

    public function getId()
    {
        if (!$this->estimate)
            return FALSE;
        return $this->estimate->Id;
    }

    public function checkLocation()
    {
        if ($this->checkEstimate()) {
            $locationFromDB = $this->settings['location'];
            if (empty($this->estimate->DepartmentRef) || !$locationFromDB)
                return TRUE;
            $arrLocationFromDB = explode(',', $locationFromDB);
            return in_array($this->estimate->DepartmentRef, $arrLocationFromDB);
        }
        return FALSE;
    }

    private function checkEstimate()
    {
        if (!$this->estimate)
            return FALSE;
        return TRUE;
    }

    private function checkDBid()
    {
        if (!$this->dbId)
            return FALSE;
        return TRUE;
    }

    public function getClientId()
    {
        if ($this->checkEstimate())
            return $this->estimate->CustomerRef;
        return FALSE;
    }

    public function getEstimateNumber()
    {
        if ($this->checkEstimate()) {
            $qbEstimateNumber = $this->estimate->DocNumber;
            if (!empty($this->settings['prefix']))
                $qbEstimateNumber = str_replace($this->settings['prefix'], '', $qbEstimateNumber);
            return $qbEstimateNumber;
        }
        return FALSE;
    }

    public function getEstimateToDB($leadId)
    {
        if ($this->checkEstimate() && $leadId) {
            $userId = 0;
            $client = $this->CI->mdl_clients->get_clients('', 'client_qb_id = ' . $this->getClientId())->row();
            $estimateNo = str_pad($leadId, 5, '0', STR_PAD_LEFT);
            $estimateNo .= "-E";
            $dateCreate = new DateTime($this->estimate->MetaData->CreateTime);
            $estimate = [
                'estimate_no' => $estimateNo,
                'lead_id' => $leadId,
                'client_id' => $client->client_id,
                'date_created' => $dateCreate->getTimestamp(),
                'status' => $this->statusesToDB[$this->getStatus()],
                'user_id' => $userId,
                'estimate_qb_id' => $this->getId(),
                'estimate_brand_id' => default_brand(),
            ];
            return $estimate;
        }
        return false;
    }

    public function getServices()
    {
        if ($this->checkEstimate()) {
            return $this->estimate->Line;
        }
        return FALSE;
    }

    public function getServicesToDB()
    {
        if ($this->checkEstimate() && $this->checkDBid()) {
            $services = $this->getServices();
            $serviceStatus = 2;
            $estimateServices = [];
            foreach ($services as $service) {
                if (isset($service->SalesItemLineDetail->ItemRef)) {
                    $serviceFromDb = $this->CI->mdl_services->find_all(['service_qb_id' => $service->SalesItemLineDetail->ItemRef]);
                    if (!$serviceFromDb)
                        $id = 0;
                    else
                        $id = $serviceFromDb[0]->service_id;
                    $estimateServices[] = [
                        'service_id' => $id,
                        'estimate_id' => $this->dbId,
                        'service_status' => $serviceStatus,
                        'service_description' => $service->Description,
                        'service_price' => $service->Amount
                    ];
                }
            }
            return $estimateServices;
        }
        return FALSE;
    }

    function getDiscountToDB()
    {
        $discount = [];
        if ($this->checkEstimate() && $this->checkDBid()) {
            $services = $this->getServices();
            foreach ($services as $service) {
                if (isset($service->DiscountLineDetail)) {
                    $amount = $service->Amount;
                    $isPersent = isset($service->DiscountLineDetail->DiscountPercent) ? true : false;
                    if ($isPersent) {
                        $amount = $service->DiscountLineDetail->DiscountPercent;
                    }
                    $discount = [
                        'discount_amount' => $amount,
                        'estimate_id' => $this->dbId,
                        'discount_percents' => $isPersent
                    ];
                }
            }
        }
        return $discount;
    }

    public function getStatus()
    {
        if ($this->checkEstimate()) {
            return $this->estimate->TxnStatus;
        }
        return FALSE;
    }

    public function checkStatus()
    {
        if ($this->getStatus() && $this->statuses)
            return in_array($this->getStatus(), $this->statuses);
        return TRUE;
    }

    public function getInvoiceQbId(){
        if ($this->checkEstimate() && !empty($this->estimate->LinkedTxn) && !empty($this->estimate->LinkedTxn->TxnId)) {
            return $this->estimate->LinkedTxn->TxnId;
        }
        return false;
    }


    public function getAllByIterator($i){
        $httpsPostBody = "select * from Estimate ORDERBY Id ASC startPosition $i maxResults 1000";
        $allData = $this->dataService->Query($httpsPostBody);
        $error = $this->checkError();
        if ($error)
            return FALSE;
        return $allData;
    }
}