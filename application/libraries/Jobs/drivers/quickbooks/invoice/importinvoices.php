<?php

class importinvoices extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_estimates_bundles');
        $this->CI->load->model('mdl_bundles_services');
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
            while (true) {
                $invoices = $this->dataService->FindAll('Invoice', $i, 500);
                $error = checkError($this->dataService);
                if (!$error) {
                    return FALSE;
                }
                if (!$invoices)
                    break;
                $paymentMethods = getPaymentMethods($this->dataService);
                foreach ($invoices as $invoice) {
                    $i++;
                    $qbId = $invoice->Id;
                    if (!empty($invoice->DepartmentRef)) {
                        $checkLocation = checkLocation($invoice->DepartmentRef);
                        if (!$checkLocation)
                            continue;
                    }
                    $checkInvoice = $this->CI->mdl_invoices->find_all(['invoice_qb_id' => $qbId]);
                    if (!is_array($checkInvoice) || empty($checkInvoice)) {
//                        $clientQbId = $this->CI->qbclientactions->getCustomerParentQbId($invoice->CustomerRef);
//                        $clientFromQB = findByIdInQB('Customer', $invoice->CustomerRef, $this->dataService);
                        $clientId = getClientId($invoice->CustomerRef);
//                        if($clientQbId == 'refresh')
//                            continue;
//                        $clientId = getClientId($clientQbId);
                        if (!$clientId)
                            continue;
                        $qbInvoiceNO = $invoice->DocNumber;
                        if (!empty($this->settings['prefix']))
                            $qbInvoiceNO = str_replace($this->settings['prefix'], '', $qbInvoiceNO);

                        //tax
                        $tax = [];
                        $invoiceQbTax = $invoice->TxnTaxDetail;
                        if ($this->settings['us']) {
                            if (!empty($invoiceQbTax->TxnTaxCodeRef)) {
                                $taxFromQB = getQBEntityById('TaxCode', $invoiceQbTax->TxnTaxCodeRef, $this->dataService);
                                if ($taxFromQB)
                                    $tax = getTaxToDbEstimate($invoiceQbTax, $taxFromQB);
                            }
                        } else {
                            if (!empty($invoiceQbTax->TaxLine)) {
                                $tax = getTaxToDbEstimateFromTaxLine($invoiceQbTax->TaxLine, $this->dataService);
                            }
                        }

                        // create Lead
                        $lead = getLeadToDB($invoice);
                        $leadId = $this->CI->mdl_leads->insert_leads($lead);
                        $leadNO = getLeadNO($leadId);
                        $this->CI->mdl_leads->update_leads($leadNO, ['lead_id' => $leadId]);
                        make_notes($clientId, 'Quickbooks: I just created a new lead "' . $leadNO['lead_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                        // create Estimate
                        $estimate = getEstimateToDB($invoice, $leadId);
                        if (count(array_filter($tax)) >= 2)
                            $estimate = array_merge($estimate, $tax);
                        if(isset($invoiceQbTax) && !empty($invoiceQbTax->TotalTax) && $invoiceQbTax->TotalTax == 0)
                            $estimateForUpdate['estimate_hst_disabled'] = 1;
                        $estimateId = $this->CI->mdl_estimates->insert_estimates($estimate);
                        make_notes($clientId, 'Quickbooks: I just created a new estimate "' . $estimate['estimate_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                        // create Estimate Services
                        $services = $invoice->Line;
                        $estimateServices = getEstimateServicesToDB($services, $estimateId, $this->dataService, $this->settings['us'], $invoice->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1);
                        foreach ($estimateServices as $estimateService) {
                            $estimateBundleServices = !empty($estimateService['bundle_records']) ? $estimateService['bundle_records'] : [];
                            unset($estimateService['bundle_records']);
                            $estimateServiceId = $this->CI->mdl_estimates->insert_estimate_service($estimateService);
                            if(!empty($estimateBundleServices)) {
                                foreach ($estimateBundleServices as $record){
                                    $estimateBundleServiceId = $this->CI->mdl_estimates->insert_estimate_service($record);
                                    if(!empty($estimateServiceId) && !empty($estimateBundleServiceId)) {
                                        $estimateBundle = [
                                            'eb_service_id' => $estimateBundleServiceId,
                                            'eb_bundle_id' => $estimateServiceId
                                        ];
                                        $this->CI->mdl_estimates_bundles->insert($estimateBundle);
                                    }
                                }
                            }
                        }
                        // create discount
                        $discount = getDiscountToDB($services, $estimateId);
                        if (is_array($discount) && !empty($discount)) {
                            $this->CI->mdl_clients->insert_discount($discount);
                        }

                        // create work orders
                        $workOrderNumber = getNO($leadId, 'W');
                        $workOrder = getWorkOrderToDB($clientId, $estimateId, $workOrderNumber, $qbInvoiceNO, $invoice);
                        $workOrderId = $this->CI->mdl_workorders->insert_workorders($workOrder);
                        make_notes($clientId, 'Quickbooks: I just created a new work order "' . $workOrderNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

                        // create invoice
                        $invoiceNumber = getNO($leadId, 'I');
                        $invoiceToDB = getInvoiceToDB($invoiceNumber, $workOrderId, $estimateId, $clientId, $invoice, 'create');
                        $invoiceToDB['invoice_notes'] = preg_replace("/.*\./Us", "", $invoiceToDB['invoice_notes'], 1);
                        $invoiceId = $this->CI->mdl_invoices->insert_invoice($invoiceToDB);
                        make_notes($clientId, 'Quickbooks: I just created a new invoice "' . $invoiceNumber . '" for the client. ', $type = 'system', $lead_id = NULL);
                        createQBLog('invoice', 'create', 'pull', $invoiceId);
                        //create deposit
                        if(!empty($invoice->Deposit)){
                            $paymentToDB = getDepositToDB($estimateId, $invoice->Deposit, $invoice->PaymentMethodRef, $paymentMethods);
                            $paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                            createQBLog('payment', 'create', 'pull', $paymentId);
                        }
                    }
                }
            }
            if ($module == 'All')
                pushJob('quickbooks/payment/importpayments', 'Payment');
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
