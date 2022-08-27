<?php

use QuickBooksOnline\API\Facades\Invoice;

class exportinvoices extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_services');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_clients');

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
            $taxesInQB = query('TaxRate', $this->dataService);
            if(!$taxesInQB)
                return FALSE;
            $taxRateRef = getTaxRateRef($this->settings['tax_rate'], $taxesInQB);
            $taxesInQB = query('TaxCode', $this->dataService);
            $taxCodeRef = getTaxCodeRef($taxesInQB, $taxRateRef);
            $payload = unserialize($job->job_payload);
            $i = $payload['count'];
            $invoices = $this->CI->mdl_invoices->find_all_with_limit([], 1000, $i, '', ['invoice_qb_id' => null]);
            if (!is_array($invoices) || empty($invoices))
            {
                pushJob('quickbooks/invoice/exportinterestinqb', serialize(['module' => 'invoice', 'action' => 'export']));
//                pushJob('quickbooks/payment/exportpayments', serialize(['module' => 'Payment', 'count' => 0, 'type' => 'invoice']));
                return TRUE;
            }
            foreach ($invoices as $invoice) {
                if (is_object($invoice)) {
                    $items = $this->CI->mdl_estimates->find_estimate_services($invoice->estimate_id, ['estimates_services.service_status' => 2]);
                    $estimate = $this->CI->mdl_estimates->find_by_id($invoice->estimate_id);
                    $hst = getHst($estimate->estimate_hst_disabled);
                    $itemsToQB = createServicesForInvoiceQB($items, $taxCodeRef, $estimate->estimate_hst_disabled, $this->settings['tax_rate'], $this->dataService);
                    $where = [
                        'id' => $invoice->id
                    ];
                    if (is_array($itemsToQB) && !empty($itemsToQB)) {
                        $discount = $this->CI->mdl_clients->get_discount(['discounts.estimate_id' => $invoice->estimate_id]);
                        $discountToQB = createDiscountForInvoiceQB($discount);
                        if ($discountToQB && $estimate->estimate_hst_disabled != 2) {
                            $itemsToQB[] = $discountToQB;
                        }
                        $customerId = $this->CI->mdl_clients->get_client_by_id($invoice->client_id)->client_qb_id;
                        $invoiceNO = $invoice->invoice_no;
                        $description = $invoice->invoice_notes;
                        $date = new DateTime($invoice->date_created);
                        $dueDate = new DateTime($invoice->overdue_date);
                        $invoiceToQB = createInvoiceForQB($customerId, $itemsToQB, $invoiceNO, $description, $date->format('Y-m-d '), $dueDate->format('Y-m-d '), $hst);
                        $invoiceObject = Invoice::create($invoiceToQB);
                        $qbId = createRecordInQBFromObject($invoiceObject, $this->dataService,false,false, $invoice->id);
                        if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                            return FALSE;
                        elseif (!$qbId) {
                            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => '']));
                            $i++;
                        } else {
                            $updateData = [
                                'invoice_qb_id' => $qbId
                            ];
                            $this->CI->mdl_invoices->update_invoice($updateData, $where);
                        }
                    }else{
                        $updateData = [
                            'invoice_qb_id' => 0
                        ];
                        $this->CI->mdl_invoices->update_invoice($updateData, $where);
                    }
                }
            }
            $invoices = $this->CI->mdl_invoices->find_all_with_limit([], 1000, $i, '', ['invoice_qb_id' => null]);
            if (is_array($invoices) && !empty($invoices))
                pushJob('quickbooks/invoice/exportinvoices', serialize(['module' => 'Invoice', 'count' => $i]));
            else
                pushJob('quickbooks/invoice/exportinterestinqb', serialize(['module' => 'invoice', 'action' => 'export']));
//                pushJob('quickbooks/payment/exportpayments', serialize(['module' => 'Payment', 'count' => 0, 'type' => 'invoice']));

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
