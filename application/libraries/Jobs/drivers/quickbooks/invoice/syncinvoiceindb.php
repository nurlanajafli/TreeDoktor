<?php

class syncinvoiceindb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $action;
    private $route;
    private $itemId;

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
        $this->route = 'pull';
    }

    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if (!$this->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $operation = $payload['operation'];
            if($operation == 'Delete') {
                $this->action = 'delete';
                $theInvoice = $this->CI->mdl_invoices->find_by_field(['invoice_qb_id' => $payload['qbId']]);
                if(empty($theInvoice))
                    return true;
                $this->itemId = $theInvoice->id;
                $this->CI->mdl_invoices->delete_invoice_new($this->itemId);
                createQBLog('invoice', $this->action, $this->route, $this->itemId);
                return TRUE;
            }
            $invoice = findByIdInQB($payload['module'], $payload['qbId'], $this->dataService);
            if (!$invoice) {
                $message = 'Error retrieving data from QuickBooks (qbId = ' . $payload['qbId'] . ')';
                createQBLog('invoice', 'get', 'pull', -1, $message);
                return FALSE;
            }
            if (!empty($invoice->DepartmentRef)) {
                $checkLocation = checkLocation($invoice->DepartmentRef);
                if (!$checkLocation)
                    return TRUE;
            }
//            $clientFromQB = findByIdInQB('Customer', $invoice->CustomerRef, $this->dataService);
            $clientId = getClientId($invoice->CustomerRef);
            if (!$clientId)
                $clientId = $this->createClientInQB($invoice->CustomerRef);
            if (!$clientId) {
                $message = 'no such client (invoiceQBId = ' . $payload['qbId'] . ')';
                createQBLog('invoice', 'get', 'pull', -1, $message);
                return FALSE;
            }
            $qbInvoiceNO = $invoice->DocNumber;

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
            //Lead
            $lead = getLeadToDB($invoice);
            //Invoice Services
            $services = $invoice->Line;
            $theInvoice = $this->CI->mdl_invoices->find_by_field(['invoice_qb_id' => $payload['qbId']]);
            if (!$theInvoice) {
                //create Lead
                $leadId = $this->CI->mdl_leads->insert_leads($lead);
                $leadNO = getLeadNO($leadId);
                $this->CI->mdl_leads->update_leads($leadNO, ['lead_id' => $leadId]);
                make_notes($clientId, 'Quickbooks: I just created a new lead "' . $leadNO['lead_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                // create Estimate
                $estimate = getEstimateToDB($invoice, $leadId);
                if (count(array_filter($tax)) >= 2)
                    $estimate = array_merge($estimate, $tax);
//                if($invoiceQbTax->TotalTax == 0)
//                    $estimateForUpdate['estimate_hst_disabled'] = 1;
                $estimateId = $this->CI->mdl_estimates->insert_estimates($estimate);
                make_notes($clientId, 'Quickbooks: I just created a new estimate "' . $estimate['estimate_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                //create Estimate Services
                $estimateServices = getEstimateServicesToDB($services, $estimateId, $this->dataService, $this->settings['us'], $invoice->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1);
                foreach ($estimateServices as $estimateService) {
                    $estimateBundleServices = !empty($estimateService['bundle_records']) ? $estimateService['bundle_records'] : [];
                    unset($estimateService['bundle_records']);
                    $estimateServiceId = $this->CI->mdl_estimates->insert_estimate_service($estimateService);
                    if (!empty($estimateBundleServices))
                        foreach ($estimateBundleServices as $record) {
                            $estimateBundleServiceId = $this->CI->mdl_estimates->insert_estimate_service($record);
                            if (!empty($estimateServiceId) && !empty($estimateBundleServiceId)) {
                                $estimateBundle = [
                                    'eb_service_id' => $estimateBundleServiceId,
                                    'eb_bundle_id' => $estimateServiceId
                                ];
                                $this->CI->mdl_estimates_bundles->insert($estimateBundle);
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
                $this->itemId = $this->CI->mdl_invoices->insert_invoice($invoiceToDB);
                make_notes($clientId, 'Quickbooks: I just created a new invoice "' . $invoiceNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

                if (!empty($invoice->Deposit)) {
                    $paymentMethods = getPaymentMethods($this->dataService);
                    $invoice->Id = 0;
                    $paymentToDB = getPaymentToDB($invoice, $estimateId, $invoice->Deposit, $paymentMethods);
                    $this->CI->mdl_clients->insert_payment($paymentToDB);
                }
                $this->action = 'create';
                // canceled sync invoice numbers if created in qb
//                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoiceDBid, 'qbId' => $payload['qbId'], 'no' => 'true']));
            } elseif ($theInvoice && $operation != 'create') {
                // update Lead
                $lead = setLeadCustomFieldsToDB([], $invoice);
                if(!empty($lead) && is_array($lead)){
                    $this->CI->mdl_leads->update_leads($lead, ['lead_id' => $theInvoice->lead_id]);
                }

                $estimateId = $theInvoice->estimate_id;
                // update tax estimate
                $estimateForUpdate = [];
                if ($tax) {
                    $theEstimate = $this->CI->mdl_estimates->find_by_id($estimateId);
                    if (!empty($theEstimate) && !empty($tax['estimate_tax_name']) && $theEstimate->estimate_tax_name != $tax['estimate_tax_name']) {
                        $estimateForUpdate = array_merge($estimateForUpdate, $tax);
                        $message = 'QuickBooks: Update TAX: was ' . $theEstimate->estimate_tax_name . ' now ' . $tax['estimate_tax_name'];
                        make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
                    }
                }
                // update estimates
                if (!empty($invoice->GlobalTaxCalculation)) {
                    $taxIncl = $invoice->GlobalTaxCalculation;
                    if ($taxIncl == 'TaxInclusive')
                        $estimateForUpdate['estimate_hst_disabled'] = 2;
                    elseif ($taxIncl == 'NotApplicable')
                        $estimateForUpdate['estimate_hst_disabled'] = 1;
                    elseif ($taxIncl == 'TaxExcluded')
                        $estimateForUpdate['estimate_hst_disabled'] = 0;
                } elseif(isset($invoiceQbTax) && isset($invoiceQbTax->TotalTax) && $invoiceQbTax->TotalTax == 0)
                    $estimateForUpdate['estimate_hst_disabled'] = 1;

                if (count($estimateForUpdate) > 0)
                    $this->CI->mdl_estimates->update_estimates($estimateForUpdate, ['estimate_id' => $estimateId]);

                // update Estimate Services
                $estimateServices = getEstimateServicesToDB($services, $estimateId, $this->dataService, $this->settings['us'], $invoice->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1);
                $invoiceNumber = $theInvoice->invoice_no;
                $servicesToMessageNotes = '';
//                $this->CI->mdl_estimates->delete_estimate_service(['estimate_id' => $estimateId]);
                $estimateRecordsFromDB = $this->CI->mdl_estimates->find_estimate_services($estimateId);
                $insert = false;
                foreach ($estimateRecordsFromDB as $estimateRecord) {
                    if (empty($estimateRecord['estimate_service_qb_id']))
                        $insert = true;
                    if ($estimateRecord['is_bundle'])
                        $this->CI->mdl_bundles_services->delete_estimates_bundles_records(['estimate_service_id' => $estimateRecord['id']]);
                }

                if ($insert)
                    $this->CI->mdl_estimates->delete_estimate_service(['estimate_id' => $estimateId]);

                foreach ($estimateServices as $estimateService) {
                    $estimateBundleServices = !empty($estimateService['bundle_records']) ? $estimateService['bundle_records'] : [];
                    unset($estimateService['bundle_records']);
                    if ($insert) {
                        $estimateServiceId = $this->CI->mdl_estimates->insert_estimate_service($estimateService);
                        $servicesToMessageNotes .= $this->getNoteServices($estimateService);
                    } else {
                        $checkInDb = false;
                        foreach ($estimateRecordsFromDB as $key => $recordFromDb) {
                            if ($estimateService['estimate_service_qb_id'] == $recordFromDb['estimate_service_qb_id']) {
                                $estimateServiceId = $recordFromDb['id'];
                                if ($estimateService['service_status'] != $recordFromDb['service_status'] || $estimateService['service_description'] != $recordFromDb['service_description'] ||
                                    $estimateService['service_price'] != $recordFromDb['service_price'] || $estimateService['non_taxable'] != $recordFromDb['non_taxable'] ||
                                    $estimateService['quantity'] != $recordFromDb['quantity'] || !empty($estimateService['estimate_service_category_id']) && $estimateService['estimate_service_category_id'] != $recordFromDb['estimate_service_category_id'] ||
                                    !empty($estimateService['estimate_service_class_id']) && $estimateService['estimate_service_class_id'] != $recordFromDb['estimate_service_class_id']) {

                                    $servicesToMessageNotes .= $this->getNoteServices($estimateService, $recordFromDb);
                                    $where = [
                                        'id' => $estimateServiceId
                                    ];
                                    unset($estimateService['service_status']);
                                    $this->CI->mdl_estimates->update_estimate_service($where, $estimateService);
                                }
                                unset($estimateRecordsFromDB[$key]);
                                $checkInDb = true;
                                break;
                            }
                        }
                        if ($checkInDb === false) {
                            $estimateServiceId = $this->CI->mdl_estimates->insert_estimate_service($estimateService);
                        }
                    }

                    if (!empty($estimateBundleServices) && isset($estimateServiceId) && !empty($estimateServiceId)) {
                        $this->CI->mdl_estimates_bundles->delete_by(['eb_bundle_id' => $estimateServiceId]);
                        foreach ($estimateBundleServices as $record) {
                            if ($insert) {
                                $estimateBundleServiceId = $this->CI->mdl_estimates->insert_estimate_service($record);
                            } else {
                                $checkInDb = false;
                                foreach ($estimateRecordsFromDB as $key => $value) {
                                    if ($record['estimate_service_qb_id'] == $value['estimate_service_qb_id']) {
                                        $estimateBundleServiceId = $value['id'];
                                        $where = [
                                            'id' => $estimateBundleServiceId
                                        ];
                                        $this->CI->mdl_estimates->update_estimate_service($where, $record);
                                        unset($estimateRecordsFromDB[$key]);
                                        $checkInDb = true;
                                        break;
                                    }
                                }
                                if ($checkInDb === false) {
                                    $estimateBundleServiceId = $this->CI->mdl_estimates->insert_estimate_service($record);

                                }
                            }
                            if (!empty($estimateServiceId) && !empty($estimateBundleServiceId)) {
                                $estimateBundle = [
                                    'eb_service_id' => $estimateBundleServiceId,
                                    'eb_bundle_id' => $estimateServiceId
                                ];
                                $this->CI->mdl_estimates_bundles->insert($estimateBundle);
                            }
                        }
                    }
                }
                if (!empty($estimateRecordsFromDB)) {
                    foreach ($estimateRecordsFromDB as $value) {
                        $this->CI->mdl_estimates->delete_estimate_service(['id' => $value['id']]);
                    }
                }
                // update discount
                $discount = getDiscountToDB($services, $estimateId);
                if (count($discount)) {
                    $theDiscount = $this->CI->mdl_clients->get_discount(['estimates.estimate_id' => $estimateId]);
                    if (!empty($theDiscount['discount_id'])) {
                        if ($theDiscount['discount_amount'] != $discount['discount_amount'] || $theDiscount['discount_percents'] != $discount['discount_percents']) {
                            $discountId = $theDiscount['discount_id'];
                            $this->CI->mdl_clients->update_discount($discountId, $discount);
                            make_notes($clientId, 'Quickbooks: Updated discount "' . str_replace('I', 'E', $invoiceNumber) . '" - ' . ($discount['discount_percents'] ? $discount['discount_amount'] : money($discount['discount_amount'])) . ($discount['discount_percents'] ? ' %' : ''), $type = 'system', $lead_id = NULL);
                        }
                    } else {
                        $this->CI->mdl_clients->insert_discount($discount);
                        make_notes($clientId, 'Quickbooks: Set discount "' . str_replace('I', 'E', $invoiceNumber) . '" - ' . ($discount['discount_percents'] ? $discount['discount_amount'] : money($discount['discount_amount'])) . ($discount['discount_percents'] ? ' %' : ''), $type = 'system', $lead_id = NULL);
                    }
                }

                // update invoice
                $workOrderId = $theInvoice->workorder_id;
                $invoice = getInvoiceToDB($invoiceNumber, $workOrderId, $estimateId, $clientId, $invoice);
                unset($invoice['invoice_notes']);
                unset($invoice['invoice_no']);
                $this->itemId = $theInvoice->id;
                $where = [
                    'id' => $this->itemId
                ];
                $this->CI->mdl_invoices->update_invoice($invoice, $where);
                if (!empty($servicesToMessageNotes)) {
                    $messageNotes = 'Services:<br>';
                    $messageNotes .= '<ul>';
                    $messageNotes .= $servicesToMessageNotes;
                    $messageNotes .= '</ul>';
                    $message = 'Quickbooks: Update estimate "' . str_replace('I', 'E', $invoiceNumber) . '"<br>';
                    $message .= $messageNotes;
                    make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
                }
                $this->action = 'update';
            }
            createQBLog('invoice', $this->action, $this->route, $this->itemId);
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    public function getNoteServices($estimateService, $recordFromDb = [])
    {
        $servicesToMessageNotes = '';
        if(empty($recordFromDb) || $estimateService['service_description'] != $recordFromDb['service_description'] || $estimateService['service_price'] != $recordFromDb['service_price']) {
            $serviceName = $this->CI->mdl_services->get($estimateService['service_id']);
            $servicesToMessageNotes .= '<li>';
            $servicesToMessageNotes .= 'Name: ' . $serviceName->service_name . '<br>';
            $servicesToMessageNotes .= 'Description: ' . $estimateService['service_description'] . '<br>';
            $servicesToMessageNotes .= 'Price: ' . $estimateService['service_price'] . '<br>';
            $servicesToMessageNotes .= '</li>';
        }
        return $servicesToMessageNotes;
    }

    public function createClientInQB($clientQbId)
    {
        $customer = findByIdInQB('Customer', $clientQbId, $this->dataService);
        if (!$customer)
            return FALSE;
        $customerArr[] = $customer;
        $clientsToDB = getAllCustomerToDB($customerArr);
        $clientsContacts = getAllClientsContactsToDB($customerArr);

        $clientId = $this->CI->mdl_clients->add_new_client_with_data($clientsToDB[0]);
        $message = 'QuickBooks: Hey, I just created a new client.';
        $clientsContacts = addClientIdToClientsContacts($clientsContacts, $clientsToDB[0]['client_name'], $clientId);

        make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
        $this->actionsWithClientsContacts($clientsContacts);
        return $clientId;
    }

    public function actionsWithClientsContacts($clientsContacts)
    {
        foreach ($clientsContacts as $clientContact) {
            $clientId = $clientContact['cc_client_id'];
            $message = '';
            $where = [
                'cc_client_id' => $clientId,
                'cc_title' => $clientContact['cc_title']
            ];
            $cc = $this->CI->mdl_clients->get_client_contact($where);
            if (!empty($cc)) {
                $this->CI->mdl_clients->update_client_contact($clientContact, $where);
                $message = getCcMessage('Update', $clientContact, $cc);
            } else {
                $this->CI->mdl_clients->add_client_contact($clientContact);
                $message = getCcMessage('Create', $clientContact);
            }
            if ($message)
                make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
        }
    }
}
