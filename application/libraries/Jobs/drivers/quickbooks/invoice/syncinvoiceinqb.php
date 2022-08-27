<?php

use QuickBooksOnline\API\Facades\Invoice;
use application\modules\clients\models\ClientsContact;

class syncinvoiceinqb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $checkServiceOrProductInInvoice = false;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_services');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_jobs');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
    }

    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        else {
            $payload = unserialize($data);
            if(empty($payload['qbId'])){
                $qbIds = [0, null, ''];
                foreach ($qbIds as $qbId){
                    $checkPayload = serialize(['id' => $payload['id'], 'qbId' => $qbId]);
                    $result = $this->CI->mdl_jobs->get_by(['job_payload' => $checkPayload, 'job_reserved_at' => 0, 'job_driver' => 'quickbooks/invoice/syncinvoiceinqb']);
                    if(!empty($result))
                        return false;
                }
            }
        }
        return $data;
    }

    public function execute($job = NULL)
    {
        if (!$this->settings['stateInQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $invoice = $this->CI->mdl_invoices->find_by_id($payload['id']);
            if (is_object($invoice)) {
                $items = $this->CI->mdl_estimates->find_estimate_services($invoice->estimate_id, ['estimates_services.service_status !=' => 1]);
                $estimate = $this->CI->mdl_estimates->find_by_id($invoice->estimate_id);
                $interestData = $invoice->interest_status == 'No' ? $this->CI->mdl_invoices->getInterestData($invoice->id) : [];
                $taxCodeRef = 'TAX';
                $taxCodeRefExempt = 'NON';
                $hst = '';
                $shipFromLead = null;
                $usaTaxCode = '';
                $taxesInQB = query('TaxRate', $this->dataService);
                $taxesCodeInQB = query('TaxCode', $this->dataService);
                if(!$this->settings['us']) {
                    $hst = getHst($estimate->estimate_hst_disabled);
                    if (!$taxesInQB)
                        return FALSE;
                    $taxRateRefExempt = getTaxRateRef(0, $taxesInQB, $taxesCodeInQB);
                    $taxRateRef = getTaxRateRef($estimate->estimate_tax_value, $taxesInQB, $taxesCodeInQB);
                    $taxCodeRefExempt = getTaxCodeRef($taxesCodeInQB, $taxRateRefExempt);
                    $taxCodeRef = getTaxCodeRef($taxesCodeInQB, $taxRateRef);
                } else {
                    $shipFromLead = getShipAddrFromCustomer($estimate);
                    if(!empty($taxesInQB)) {
                        $taxRateRef = getTaxRateRef($estimate->estimate_tax_value, $taxesInQB, $taxesCodeInQB);
                        $usaTaxCode = getTaxCodeRef($taxesCodeInQB, $taxRateRef);
                    }
                }

                $discount = $this->CI->mdl_clients->get_discount(['discounts.estimate_id' => $invoice->estimate_id]);
                $discountToQB = createDiscountForInvoiceQB($discount);

                if (count($interestData)) {
                    $paymentsData = $this->CI->mdl_clients->get_payments(array('client_payments.estimate_id' => $invoice->estimate_id));
                    $interestItems = $this->getInterest($interestData, $invoice->interest_status, isset($discount['discount_percents']) ? $discount['discount_percents'] : null, isset($discount['discount_amount']) ? $discount['discount_amount'] : null, $items, $paymentsData, $estimate->estimate_tax_rate, $estimate->estimate_hst_disabled == 2 ? true : false);
                    if (count($interestItems))
                        $items = array_merge($items, $interestItems);
                }

                $itemsToQB = createServicesForInvoiceQB($items, $taxCodeRef, $estimate->estimate_hst_disabled, $estimate->estimate_tax_value, $this->dataService, $taxCodeRefExempt);
                if (!$itemsToQB)
                    return FALSE;
                elseif ($this->settings['us'] && !isset($payload['reset'])){
                    foreach ($itemsToQB as $item){
                        if(isset($item['DetailType']) && $item['DetailType'] != 'GroupLineDetail'){
                            $this->checkServiceOrProductInInvoice = true;
                        }
                    }
                    if($this->checkServiceOrProductInInvoice === false){
                        $arrToQB= [];
                        $result = $this->CI->mdl_services->find_all(['service_qb_id >' => '0', 'is_bundle' => 0]);
                        if(isset($result[0]) && !empty($result[0]->service_qb_id)) {
                            $serviceQbId = $result[0]->service_qb_id;
                            $arrToQB = addServiceToBundle($serviceQbId);
                        }
                        if(count($arrToQB))
                            $itemsToQB[] = $arrToQB;
                    }
                }

                if ($discountToQB && $estimate->estimate_hst_disabled != 2) {
                    $itemsToQB[] = $discountToQB;
                }
                $customerFromDB = $this->CI->mdl_clients->get_client_by_id($invoice->client_id);
                $customerId = $customerFromDB->client_qb_id;

//                // set a child client qb_id
//                if(!empty($estimate->lead_cc_id))
//                    $ccFromDb = ClientsContact::where([['cc_id' , '=', $estimate->lead_cc_id], ['cc_qb_id', '>', 1]])->first();
//                if(!empty($ccFromDb))
//                    $customerId = $ccFromDb['cc_qb_id'];

                if (!$customerId) {
                    $customerId = createClientInQB($customerFromDB, $this->dataService);
                    if (!$customerId)
                        return FALSE;
                }
                $invoiceNO = $invoice->invoice_no;
                if (!empty($this->settings['prefix']))
                    $invoiceNO = $this->settings['prefix'] . $invoiceNO;
                $description = $invoice->invoice_notes;
                $date = new DateTime($invoice->date_created);
                $dueDate = new DateTime($invoice->overdue_date);

                $clientContact = $this->CI->mdl_clients->get_client_contact(['cc_client_id' => $customerFromDB->client_id, 'cc_print' => 1]);
                $email = $clientContact['cc_email'] ?? '';
                $invoiceToQB = createInvoiceForQB($customerId, $itemsToQB, $invoiceNO, $description, $date->format('Y-m-d '), $dueDate->format('Y-m-d '), $hst, $this->settings['location'], $this->settings['us'], $shipFromLead, $this->settings['stateSyncInvoiceNO'], $estimate, $invoice->in_status == 3 ? true : false, $email);
                if($this->settings['us'] && !empty($usaTaxCode))
                    $invoiceToQB['TxnTaxDetail'] = [
                        'TxnTaxCodeRef' => $usaTaxCode
                    ];
            }
            if (!is_object($invoice) && empty($invoice) && $payload['qbId']) {
                $theInvoice = getQBEntityById('Invoice', $payload['qbId'], $this->dataService);
                if (!$theInvoice)
                    return FALSE;
                $qbResponse = deleteRecordInQBFromObject($theInvoice, $this->dataService, $payload['id']);
                if($qbResponse === false)
                    return FALSE;
            } elseif (!$invoice->invoice_qb_id) {
                $invoiceNO = $this->settings['stateSyncInvoiceNO'] ? false : true;
                $invoiceObject = Invoice::create($invoiceToQB);
                $qbResponse = createRecordInQBFromObject($invoiceObject, $this->dataService, $invoiceNO, true, $invoice->id);
                $where = [
                    'id' => $invoice->id
                ];
                if ($qbResponse == 'AuthenticationFailed' || $qbResponse == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbResponse) {
                    $updateData = [
                        'invoice_qb_id' => 0
                    ];
                    $this->CI->mdl_invoices->update_invoice($updateData, $where);
                } elseif (is_array($qbResponse)) {
                    $updateData = [
                        'invoice_qb_id' => $qbResponse['id'],
                        'qb_invoice_no' => $qbResponse['invoiceNO']
                    ];
                } else {
                    $updateData = [
                        'invoice_qb_id' => $qbResponse->Id
                    ];
                }
                $this->CI->mdl_invoices->update_invoice($updateData, $where);
                if(isset($qbResponse->Line) && !empty($qbResponse->Line) && !empty($items)) {
                    $estimateService = getQbEstimateServicesForAddQbIdToDb($items, $qbResponse->Line);
                    if(!empty($estimateService))
                        foreach ($estimateService as $key => $value){
                            $updateData = [
                                'estimate_service_qb_id' => $value['estimate_service_qb_id']
                            ];
                            $where = [
                                'id' =>  $value['id']
                            ];
                            $this->CI->mdl_estimates->update_estimate_service($where, $updateData);
                        }
                }
            } elseif ($invoice->invoice_qb_id) {
                $theInvoice = getQBEntityById('Invoice', $invoice->invoice_qb_id, $this->dataService);
                if (!$theInvoice)
                    return FALSE;
                if(!isset($payload['reset']) && !isset($invoiceToQB['TxnTaxDetail']))
                    $invoiceToQB['TxnTaxDetail'] = [];
                // canceled sync invoice numbers if created in qb
//                if(!isset($payload['no']))
                unset($invoiceToQB['DocNumber']);
                unset($invoiceToQB['CustomerRef']);
                $updateInvoice = Invoice::update($theInvoice, $invoiceToQB);
                $qbResponse = updateRecordInQBFromObject($updateInvoice, $this->dataService, true, $invoice->id);
                if (!$qbResponse) {
                    return FALSE;
                }
                if(isset($qbResponse->Line) && !empty($qbResponse->Line) && !empty($items)) {
                    $estimateService = getQbEstimateServicesForAddQbIdToDb($items, $qbResponse->Line);
                    if(!empty($estimateService))
                        foreach ($estimateService as $key => $value){
                            if(!empty($value['estimate_service_qb_id'])) {
                                $updateData = [
                                    'estimate_service_qb_id' => $value['estimate_service_qb_id']
                                ];
                                $where = [
                                    'id' => $value['id']
                                ];
                                $this->CI->mdl_estimates->update_estimate_service($where, $updateData);
                            }
                        }
                }
            }
            if($this->settings['us'] && $this->checkServiceOrProductInInvoice === false && !isset($payload['reset']) && !empty($invoice))
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $payload['id'], 'qbId' => !empty($payload['qbId']) ? $payload['qbId'] : $invoice->invoice_qb_id, 'reset' => true]));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    public function getInterest($interestData, $interestStatus, $discountPercents, $discount, $items, $paymentsData, $taxRate, $includedInThePrice = false)
    {
        $result = [];
        if (count($interestData) && $interestStatus != 'Yes') {
            $interestItemDBId = createOrUpdateInterestItem($this->dataService);
            $servicesAmount = getServicesAmount($items);
            $allOverdue = 0;
            foreach ($interestData as $interestKey => $interestValue) {
                if($includedInThePrice)
                    $overdueAmt = $servicesAmount / $taxRate;
                else
                    $overdueAmt = $discountPercents ? $servicesAmount - ($servicesAmount * ($discount / 100)) : $servicesAmount - $discount;
                $overdueAmt += $allOverdue;
                $clientData = $this->CI->mdl_clients->find_by_id($interestValue->client_id);
                $term = \application\modules\invoices\models\Invoice::getInvoiceTerm($clientData->client_type);
                if ($paymentsData && count($paymentsData)) {
                    foreach ($paymentsData as $pay) {
                        if ($pay['payment_date'] < (strtotime($interestValue->overdue_date) - $term * 86400))
                            $overdueAmt -= $pay['payment_amount'];
                    }
                }
                $interest = abs($interestValue->rate / 100);
                $allOverdue += $overdue = round($overdueAmt * $interest, 2);
                if($includedInThePrice)
                    $overdue *= $taxRate;
                $result[] = createServiceForInvoiceInterestQB($overdue, $discountPercents, $discount ?? null, $interestItemDBId, $term, $interestValue->overdue_date, $interestValue->rate);
            }
        }
        return $result;
    }
}