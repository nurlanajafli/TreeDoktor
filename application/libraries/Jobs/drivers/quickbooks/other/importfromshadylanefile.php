<?php


use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use application\modules\invoices\models\Invoice;
use application\modules\leads\models\Lead;
use application\modules\leads\models\LeadStatus;
use application\modules\payments\models\ClientPayment;
use application\modules\workorders\models\Workorder;

class importfromshadylanefile extends CI_Driver implements JobsInterface
{
    private $CI;
    private $checkClientByNameAddress = true;
    private $setClientQbId = false;
    private $insertInvoices = false;
    private $timeLimit = false;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_estimates_bundles');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->library('Common/EstimateActions');
        $this->CI->load->library('Common/QuickBooks/QBEstimateActions');

    }

    public function getPayload($data = NULL)
    {
        return $data;
    }

    public function execute($job = NULL)
    {

        $file = bucket_read_file('uploads/import_files/import.csv');
//        file_put_contents(storage_path('file.csv'), $file);
        ini_set('memory_limit', '500M');
//        $file = file_get_contents(storage_path('file.csv'));
//        $file = file_get_contents('/home/ivan/Загрузки/ShadyLaneExport_3_18.csv');


        $lines = explode(PHP_EOL, $file);
        $header = array_shift($lines);

        $header = explode("\t", $header);
//        debug2($header);


        $firstKey = array_search('First', $header);
        $lastKey = array_search('Last', $header);
        $nameKey = array_search('Name', $header);
        $clientAddressKey = array_search('Address', $header);
        $clientCityKey = array_search('City', $header);
        $clientStateKey = array_search('Region', $header);
        $clientZipKey = array_search('PostalCode', $header);
        $clientQbIdKey = array_search('CustNum', $header);

        $companyNameKey = array_search('Company', $header);

        $phoneKey1 = array_search('Phone1', $header);
        $phoneKey2 = array_search('Phone2', $header);
        $phoneKey3 = array_search('Phone3', $header);
        $phoneKey4 = array_search('Phone4', $header);
        $phoneType1 = array_search('Phone1Type', $header);
        $phoneType2 = array_search('Phone2Type', $header);
        $phoneType3 = array_search('Phone3Type', $header);
        $phoneType4 = array_search('Phone4Type', $header);

        $emailKey = array_search('Email', $header);
        $emailKey2 = array_search('Email2', $header);

        $serviceNameKey = array_search('TaskName', $header);

        $leadAddressKey = array_search('JobSiteAddress', $header);
        $leadCityKey = array_search('JobSiteCity', $header);
        $leadStateKey = array_search('JobSiteState', $header);
        $leadZipKey = array_search('JobSiteZipCode', $header);

        $leadStatus = LeadStatus::where(['lead_status_estimated' => 1])->get()->first();
        $dateKey = array_search('BidDate', $header);
        $statusKey = array_search('JobStatus', $header);
        $taxedKey = array_search('Taxed', $header);
        $priceKey = array_search('Price', $header);
        $discountKey = array_search('JobDiscount', $header);

        $woDateKey = array_search('AcceptedDate', $header);
        $invoiceNumKey = array_search('Invoice', $header);
        $invoiceDueKey = array_search('InvoiceDueDate', $header);
        $invoiceDateKey = array_search('InvoiceDate', $header);
        $invoiceStatusKey = array_search('PaidStatus', $header);
        $paymentDateKey = array_search('PaidDate', $header);
        $totalAmountKey = array_search('Total', $header);

        $accountBalanceKey = array_search('AccountBalance', $header);
        $depositKey = array_search('PrepaymentBalance', $header);

        $estimateServiceDescriptionKey = array_search('SpecialInstruction', $header);
        $estimateQbKey = array_search('JobNum', $header);

        $key = 1;
        $estimatesArray = [];
        $resultArray = [];
        foreach($lines as $line) {
//            debug2($key);

            $key++;
//            if($key < 111263)
//                continue;



            $row = explode("\t", $line);
            if(empty($row) || !is_array($row) || !isset($row[$firstKey]))
                continue;

            // change wo date
//            $estimates = Estimate::where(['estimate_qb_id' => $row[$estimateQbKey]])->get()->toArray();
//            foreach ($estimates as $estimate) {
//                $wo = Workorder::where('estimate_id', $estimate['estimate_id'])->get()->first();
//                if(!empty($wo) && !empty($row[$woDateKey]) && $row[$woDateKey] != 'NULL'){
//                    $dateCreate = new DateTime($row[$woDateKey]);
//                    $dateCreate = $dateCreate->format('Y-m-d');
//                    $wo->date_created = $dateCreate;
//                    $wo->save();
//                }
//            }
//
//            continue;


//            if($row[$firstKey] != 'Baldassara' && $row[$lastKey] != 'Adriana')
//                continue;

            //add a deposit

//            if(!empty($row[$depositKey]) && $row[$depositKey] != 'NULL' && $row[$depositKey] < 0) {
//                if(in_array($row[$clientQbIdKey], $estimatesArray)) {
//                    continue;
//                }
//                $dateString = $row[$dateKey];
//                if($dateString == "NULL")
//                    $dateString = $row[$dateKey+1];
//                try {
//                    $dateCreate = new DateTime($dateString);
//                } catch (Exception $e){
//                    continue;
//                }
//
//                if($this->timeLimit !== false && $this->timeLimit > $dateCreate->getTimestamp() || $row[$invoiceNumKey] != 'NULL' && !$this->insertInvoices)
//                    continue;
//
//                $estimatesArray[] = $row[$clientQbIdKey];
//                $deposit = $row[$depositKey] * -1;
//
//                $clientQbId = $row[$clientQbIdKey];
//                $companyName = (!empty($row[$companyNameKey]) && $row[$companyNameKey] != 'NULL' && mb_strtolower($row[$companyNameKey]) != 'moved') ? $row[$companyNameKey] : '';
//                $clientName = $row[$firstKey] . ' ' . $row[$lastKey];
//                $clientAddress = str_replace('"', '', $row[$clientAddressKey]);
//                if($this->checkClientByNameAddress){
//                    if($companyName)
//                        $client = Client::where('client_name', $companyName)->where('client_address', $clientAddress)->get()->first();
//                    else {
//                        $orClientName = $row[$firstKey] . ', ' . $row[$lastKey];
//                        $client = Client::where(function ($query) use ($clientName, $orClientName) {
//                            $query->where('client_name', $clientName)
//                                ->orWhere('client_name', $orClientName);
//                        })->where('client_address', $clientAddress)->get()->first();
//                    }
//                } else {
//                    $client = Client::where('client_qb_id', $clientQbId)->get()->first();
//                }
//                $resultArray[] = [
//                    'client_id' => $client->client_id,
//                    'amount' => $deposit
//                ];
//                if($key == 11170)
//                    die(json_encode($resultArray));
//                continue;
//
//                $accountBalance = $row[$accountBalanceKey];
//
//                $estimates = Estimate::with('invoice')->where(['estimate_qb_id' => $row[$estimateQbKey]])->get();
//
//                foreach ($estimates as $estimate) {
////                    echo($key);
////                    echo(count($estimates));
////                    echo($deposit);
////                    echo('===');
////                    echo($accountBalance);
//                    if(!empty($estimate->invoice) && $estimate->invoice->in_status == 4){
//                        continue;
//                    }
//                    $estimate = Estimate::where('estimates.estimate_id', $estimate->estimate_id)->withTotals(null, ['estimates.estimate_id' => [$estimate->estimate_id]])->get()->first();
//
//                    if(empty($estimate) || empty($estimate->total_due)){
//                        continue;
//                    }
//
//                    $checkDeposit = false;
//                    if($accountBalance == $deposit) {
//                        $payments = ClientPayment::where('estimate_id', $estimate->estimate_id)->get()->toArray();
//                        foreach ($payments as $payment) {
//                            if ($payment['payment_amount'] == $deposit && $payment['payment_type'] == 'deposit')
//                                $checkDeposit = true;
//                        }
//                    }
//
//
////                    if($checkDeposit == false){
//
//
//
////                        $paymentToDB = [
////                            'estimate_id' => $estimate['estimate_id'],
////                            'payment_method_int' => 1,
////                            'payment_date' => '',
////                            'payment_amount' => $deposit,
////                            'payment_checked' => 1,
////                            'payment_type' => 'deposit',
////                            'payment_qb_id' => 0
////                        ];
////                        $this->CI->mdl_clients->insert_payment($paymentToDB);
////                    }
//                }
//            }
//
//            if($key == 115247)
//                die(json_encode($resultArray));
//            continue;

            $client = null;
            // client
            if($clientQbIdKey !== false && isset($row[$clientQbIdKey])) {

                $dateString = $row[$dateKey];
                if($dateString == "NULL")
                    $dateString = $row[$dateKey+1];
                try {
                    $dateCreate = new DateTime($dateString);
                } catch (Exception $e){
                    continue;
                }

                if($this->timeLimit !== false && $this->timeLimit > $dateCreate->getTimestamp() || $row[$invoiceNumKey] != 'NULL' && !$this->insertInvoices)
                    continue;

                $clientQbId = $row[$clientQbIdKey];
                $companyName = (!empty($row[$companyNameKey]) && $row[$companyNameKey] != 'NULL' && mb_strtolower($row[$companyNameKey]) != 'moved') ? $row[$companyNameKey] : '';
                $clientName = $row[$firstKey] . ' ' . $row[$lastKey];
                $clientAddress = str_replace('"', '', $row[$clientAddressKey]);
                if($this->checkClientByNameAddress){
                    if($companyName)
                        $client = Client::where('client_name', $companyName)->where('client_address', $clientAddress)->get()->first();
                    else {
                        $orClientName = $row[$firstKey] . ', ' . $row[$lastKey];
                        $client = Client::where(function ($query) use ($clientName, $orClientName) {
                            $query->where('client_name', $clientName)
                                ->orWhere('client_name', $orClientName);
                        })->where('client_address', $clientAddress)->get()->first();
                    }
                } else {
                    $client = Client::where('client_qb_id', $clientQbId)->get()->first();
                }

                if(empty($client)) {
                    $clientArr = [];
                    if($this->setClientQbId)
                        $clientArr['client_qb_id'] = $clientQbId;
                    $clientArr['client_type'] = !empty($companyName) ? 2 : 1;
                    if ($firstKey !== false && isset($row[$firstKey]))
                        $clientArr['client_name'] = $companyName ?: $clientName;
                    if ($clientAddressKey !== false && isset($row[$clientAddressKey]))
                        $clientArr['client_address'] = $clientAddress;
                    if ($clientCityKey !== false && isset($row[$clientCityKey]))
                        $clientArr['client_city'] = str_replace('"', '', $row[$clientCityKey]);
                    if ($clientStateKey !== false && isset($row[$clientStateKey]))
                        $clientArr['client_state'] = $row[$clientStateKey];
                    if ($clientZipKey !== false && isset($row[$clientZipKey]))
                        $clientArr['client_zip'] = $row[$clientZipKey];

                    if(empty($clientArr['client_name']))
                        $clientArr['client_name'] = $clientArr['client_address'] . ', ' . $clientArr['client_city'] . ', ' . $clientArr['client_state'] . ', ' . $clientArr['client_zip'];

                    $clientArr['client_name'] = str_replace('"', '', $clientArr['client_name']);
                    $client = Client::create($clientArr);

                    if(!empty($client)){
                        $clientContacts = [];
                        $name = $clientName ?: $companyName;
                        $print = 1;
                        if(!empty($row[$phoneKey1]) && $row[$phoneKey1] != "NULL"){
                            $title = (!empty($row[$phoneType1]) && $row[$phoneType1] != "NULL") ? $row[$phoneType1] : 'Contact #1';
                            $email = (!empty($row[$emailKey]) && $row[$emailKey] != "NULL")  ? $row[$emailKey] : $row[$emailKey2];
                            $clientContacts[] = $this->getClientContact($title, $name, preg_replace('/[^0-9]/', '', $row[$phoneKey1]), $email != "NULL" ? $email : '', $client->client_id, $print);
                            $print = 0;
                        }
                        if(!empty($row[$phoneKey2]) && $row[$phoneKey2] != "NULL"){
                            $title = (!empty($row[$phoneType2]) && $row[$phoneType2] != "NULL") ? $row[$phoneType2] : 'Contact #2';
                            $clientContacts[] = $this->getClientContact($title, $name, preg_replace('/[^0-9]/', '', $row[$phoneKey2]), (!empty($row[$emailKey2]) && $row[$emailKey2] != "NULL")  ? $row[$emailKey] : '', $client->client_id, $print);
                            $print = 0;
                        }
                        if(!empty($row[$phoneKey3]) && $row[$phoneKey3] != "NULL"){
                            $title = (!empty($row[$phoneType3]) && $row[$phoneType3] != "NULL") ? $row[$phoneType3] : 'Contact #3';
                            $clientContacts[] = $this->getClientContact($title, $name, preg_replace('/[^0-9]/', '', $row[$phoneKey3]), (!empty($row[$emailKey2]) && $row[$emailKey2] != "NULL")  ? $row[$emailKey] : '', $client->client_id, $print);
                            $print = 0;
                        }
                        if(!empty($row[$phoneKey4]) && $row[$phoneKey4] != "NULL"){
                            $title = (!empty($row[$phoneType4]) && $row[$phoneType4] != "NULL") ? $row[$phoneType4] : 'Contact #4';
                            $clientContacts[] = $this->getClientContact($title, $name, preg_replace('/[^0-9]/', '', $row[$phoneKey4]), (!empty($row[$emailKey2]) && $row[$emailKey2] != "NULL")  ? $row[$emailKey] : '', $client->client_id, $print);
                        }

                        ClientsContact::insert($clientContacts);
                    }
                }

            }

//            debug2($row[$serviceNameKey]);die;
            // item
            if($serviceNameKey !== false && isset($row[$serviceNameKey])){
                $serviceName = $row[$serviceNameKey];
                if(empty($serviceName) || $serviceName == 'NULL')
                    $serviceName = 'Service';
                $service = Service::where('service_name', $serviceName)->get()->first();
                if(empty($service)){
                    $service = Service::create(['service_name' => $serviceName]);
                }
            }

            if(!empty($client)) {

                $checkEstimateInDB = false;
                $estimate = Estimate::where(['estimate_qb_id' => $row[$estimateQbKey]])->get()->toArray();
                //lead

                if(!empty($estimate)){
                    $invoiceQbId = $row[$invoiceNumKey];
                    foreach ($estimate as $est) {
                        $invoiceFromDB = Invoice::where('estimate_id', $est['estimate_id'])->get()->first();
                        if (!empty($invoiceFromDB) && $invoiceFromDB->invoice_qb_id == $invoiceQbId) {
                            $estimate = Estimate::where('estimate_id', $est['estimate_id'])->get()->first();
                        }
                    }

                    if(is_array($estimate) && !empty($invoiceQbId) && $invoiceQbId != 'NULL')
                        $estimate = null;
                    else {
                        if(is_array($estimate))
                            $estimate = Estimate::where('estimate_qb_id', $row[$estimateQbKey])->get()->first();
                        if(!empty($estimate)) {
                            $lead = Lead::find($estimate->lead_id);
                            $checkEstimateInDB = true;
                        }
                    }
                }

                if(empty($estimate)) {
                    $lead = [
                        'client_id' => $client->client_id,
                        'lead_address' => $row[$leadAddressKey],
                        'lead_city' => $row[$leadCityKey],
                        'lead_state' => $row[$leadStateKey],
                        'lead_zip' => $row[$leadZipKey],
                        'lead_status' => 'Estimated',
                        'lead_reffered_by' => 'Quickbooks desktop',
                        'lead_status_id' => $leadStatus->lead_status_id,
                        'lead_date_created' => $dateCreate->format('Y-m-d H:i:s')
                    ];
                    $lead = Lead::create($lead);
                    $leadNO = getLeadNO($lead->lead_id);
                    Lead::where('lead_id', $lead->lead_id)->update($leadNO);
                }

                if(!empty($lead)) {
                    //estimate
                    $status = 1;
                    $serviceStatus = 0;
                    if ($row[$statusKey] !== false) {
                        if ($row[$statusKey] == 'Completed' || $row[$statusKey] == 'Work Order') {
                            $status = 6;
                        }
                        if ($row[$statusKey] == 'Declined' || $row[$statusKey] == 'Cancelled') {
                            $status = 4;
                            $serviceStatus = 1;
                        }
                        if ($row[$statusKey] == 'Hold')
                            $status = 8;
                    }

                    if(!$checkEstimateInDB) {
                        $estimateNo = str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT);
                        $estimateNo .= "-E";

                        $estimate = [
                            'estimate_no' => $estimateNo,
                            'lead_id' => $lead->lead_id,
                            'estimate_brand_id' => default_brand(),
                            'client_id' => $client->client_id,
                            'date_created' => $dateCreate->getTimestamp(),
                            'status' => $status,
                            'user_id' => 0,
                            'estimate_qb_id' => $row[$estimateQbKey],
                            'estimate_tax_name' => 'TAX',
                            'estimate_tax_rate' => '1.13',
                            'estimate_tax_value' => '13',
                        ];
                        $estimate = Estimate::create($estimate);
                    }

                    if(!empty($estimate)){
                        //estimate service
                        if(!empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != NULL && $row[$invoiceNumKey] != 'NULL')
                            $serviceStatus = 2;

                        $record = [
                            'service_id' => $service->service_id,
                            'estimate_id' => $estimate->estimate_id,
                            'service_status' => $serviceStatus,
                            'service_description' => strip_tags($row[$estimateServiceDescriptionKey]),
                            'service_price' => $row[$priceKey],
                            'non_taxable' => $row[$taxedKey] === 0 || $row[$taxedKey] === '0' ? 1 : 0,
                            'quantity' => 1
                        ];
                        EstimatesService::insert($record);

                        if($checkEstimateInDB)
                            continue;

                        if($row[$discountKey] > 0){
                            $discount = [
                                'discount_amount' => $row[$discountKey] * 100,
                                'estimate_id' => $estimate->estimate_id,
                                'discount_percents' => 1
                            ];
                            $this->CI->mdl_clients->insert_discount($discount);
                        }

                        if($status == 6){
                            $workOrderNo = getNO($lead->lead_id, 'W');
                            if(!empty($row[$woDateKey]) && $row[$woDateKey] != "NULL"){
                                $dateCreate = new DateTime($row[$woDateKey]);
                                $dateCreate = $dateCreate->format('Y-m-d');
                            }
                            elseif(!empty($row[$dateKey]) && $row[$dateKey] != "NULL"){
                                $dateCreate = new DateTime($row[$dateKey]);
                                $dateCreate = $dateCreate->format('Y-m-d');
                            }
                            if(!empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != NULL && $row[$invoiceNumKey] != 'NULL' || $dateCreate < '2016-12-01')
                                $statusId = $this->CI->mdl_workorders->getFinishedStatusId();
                            else
                                $statusId = $this->CI->mdl_workorders->getDefaultStatusId();

                            $workOrder = [
                                'workorder_no' => $workOrderNo,
                                'estimate_id' => $estimate->estimate_id,
                                'client_id' => $client->client_id,
                                'wo_status' => $statusId,
                                'date_created' => isset($dateCreate) ? $dateCreate : new DateTime()
                            ];
                            $workOrderId = $this->CI->mdl_workorders->insert_workorders($workOrder);

                            if(!empty($workOrderId) && !empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != 'NULL' || ($statusId == 0 && $dateCreate < '2016-12-01')){
                                $invoiceNO = getNO( $lead->lead_id, 'I');
                                if(!empty($row[$invoiceDueKey]) && $row[$invoiceDueKey] != 'NULL'){
                                    $overdue = new DateTime($row[$invoiceDueKey]);
                                    $overdue = $overdue->format('Y-m-d');
                                }
                                if(!empty($row[$invoiceDateKey]) && $row[$invoiceDateKey] != 'NULL'){
                                    $dateCreate = new DateTime($row[$invoiceDateKey]);
                                    $dateCreate = $dateCreate->format('Y-m-d');
                                }
                                $status = 1;
                                if(!empty($row[$invoiceStatusKey]) && $row[$invoiceStatusKey] == 'Paid' || ($statusId == 0 && $dateCreate < '2016-12-01')){
                                    $status = 4;
                                }
                                $invoice = [
                                    'invoice_no' => $invoiceNO,
                                    'workorder_id' => $workOrderId,
                                    'estimate_id' => $estimate->estimate_id,
                                    'client_id' => $client->client_id,
                                    'in_status' => $status,
                                    'date_created' => isset($dateCreate) ? $dateCreate : '',
                                    'overdue_date' => isset($overdue) ? $overdue : '',
                                    'invoice_notes' => '',
                                    'invoice_qb_id' => $row[$invoiceNumKey]
                                ];
                                $this->CI->mdl_invoices->insert_invoice($invoice);

                                if($status == 4) {
                                    unset($dateCreate);
                                    if(!empty($row[$paymentDateKey]) && $row[$paymentDateKey] != 'NULL'){
                                        $dateCreate = new DateTime($row[$paymentDateKey]);
                                    }
                                    $total = (!empty($row[$totalAmountKey]) && $row[$totalAmountKey] > 0 && $row[$totalAmountKey] !== 'NULL') ? $row[$totalAmountKey] : $this->CI->mdl_estimates->get_total_estimate_balance( $estimate->estimate_id);
                                    $paymentToDB = [
                                        'estimate_id' => $estimate->estimate_id,
                                        'payment_method_int' => 1,
                                        'payment_date' => isset($dateCreate) ? $dateCreate->getTimestamp() : '',
                                        'payment_amount' => $total,
                                        'payment_checked' => 1,
                                        'payment_type' => 'invoice',
                                        'payment_qb_id' => 0
                                    ];
                                    $this->CI->mdl_clients->insert_payment($paymentToDB);
                                }
                            }
                        }
                    }
                }
            }

        }
        unset($file, $lines);
        return true;
    }

    private function getClientContact($title, $name, $phone, $email, $clientId, $print = 0){
        return  [
            'cc_title' => $title,
            'cc_name' => $name,
            'cc_phone' => $phone,
            'cc_email' => $email,
            'cc_print' => $print,
            'cc_client_id' => $clientId
        ];
    }
}