<?php


class importestimates extends CI_Driver implements JobsInterface
{
    private $CI;
    private $dataService;
    private $estimateDB;
    private $estimateQB;
    private $settings;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_estimates_bundles');
        $this->CI->load->library('Common/EstimateActions');
        $this->CI->load->library('Common/QuickBooks/QBEstimateActions');
        $this->estimateQB = $this->CI->qbestimateactions;
        $this->dataService = $this->CI->qbestimateactions->dataService;

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
    }

    public function getPayload($data = NULL)
    {
        if (!$data || !$this->CI->qbestimateactions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if ($job) {
            $i = 1;
            while (true) {
                $estimates = $this->CI->qbestimateactions->getAllByIterator($i);
                if ($estimates === FALSE)
                    return FALSE;
                elseif (!$estimates)
                    break;

                foreach ($estimates as $estimate) {
                    $i++;
                    $this->estimateQB->set($estimate);
                    if (!$this->estimateQB->checkLocation() || !$this->estimateQB->checkStatus() || ($this->estimateQB->getStatus() == 'Accepted' && $this->estimateQB->getInvoiceQbId()))
                        continue;

                    //tax
                    $tax = [];
                    $invoiceQbTax = $estimate->TxnTaxDetail;
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

                    $clientId = getClientId($this->estimateQB->getClientId());
                    $checkEstimate = $this->CI->mdl_estimates->find_all(['estimate_qb_id' => $this->estimateQB->getId()]);

                    if (!$clientId || count($checkEstimate))
                        continue;

                    // create Lead
                    $lead = getLeadToDB($estimate);
                    $leadId = $this->CI->mdl_leads->insert_leads($lead);
                    $leadNO = getLeadNO($leadId);
                    $this->CI->mdl_leads->update_leads($leadNO, ['lead_id' => $leadId]);
                    make_notes($clientId, 'Quickbooks: I just created a new lead "' . $leadNO['lead_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

//                    $estimateToDB = getEstimateToDB($estimate, $leadId);
                    $estimateToDB = $this->estimateQB->getEstimateToDB($leadId);
                    if (count(array_filter($tax)) >= 2)
                        $estimateToDB = array_merge($estimateToDB, $tax);
                    if($invoiceQbTax->TotalTax == 0)
                        $estimateForUpdate['estimate_hst_disabled'] = 1;

                    $estimateToDB['estimate_qb_id'] = $this->estimateQB->getId();
                    $estimateId = $this->CI->mdl_estimates->insert_estimates($estimateToDB);
                    make_notes($clientId, 'Quickbooks: I just created a new estimate "' . $estimateToDB['estimate_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                    // create Estimate Services
                    $services = $estimate->Line;
                    $estimateServices = getEstimateServicesToDB($services, $estimateId, $this->dataService, $this->settings['us'], $estimate->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1, 0);
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
//                    $discount = $this->estimateQB->getDiscountToDB();
//                    if (count($discount) > 0) {
//                        $this->CI->mdl_clients->insert_discount($discount);
//                    }
                    $discount = getDiscountToDB($services, $estimateId);
                    if (is_array($discount) && !empty($discount)) {
                        $this->CI->mdl_clients->insert_discount($discount);
                    }

                    if($this->estimateQB->getStatus() == 'Accepted') {
                        // create work order
                        $workOrderNumber = getNO($leadId, 'W');
                        $dateCreate = new DateTime($estimate->TxnDate);
                        $workOrder = getWorkOrderToDB($clientId, $estimateId, $workOrderNumber, null, null, $dateCreate->format('Y-m-d'), true);
                        $this->CI->mdl_workorders->insert_workorders($workOrder);
                        make_notes($clientId, 'Quickbooks: I just created a new work order "' . $workOrderNumber . '" for the client. ', $type = 'system', $lead_id = NULL);
                    }
                }
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}