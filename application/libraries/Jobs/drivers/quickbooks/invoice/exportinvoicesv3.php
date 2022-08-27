<?php

use QuickBooksOnline\API\Facades\Invoice;
use application\modules\clients\models\ClientsContact;

class exportinvoicesv3 extends CI_Driver implements JobsInterface
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
            $payload = unserialize($job->job_payload);
            $module = $payload['module'];
            $taxCodeRef = $this->settings['tax_rate'] > 0 ? 'TAX' : 'NON';
            if(!$this->settings['us']) {
                $taxesInQB = query('TaxRate', $this->dataService);
                if (!$taxesInQB)
                    return FALSE;
                $taxRateRef = getTaxRateRef($this->settings['tax_rate'], $taxesInQB);
                $taxesInQB = query('TaxCode', $this->dataService);
                $taxCodeRef = getTaxCodeRef($taxesInQB, $taxRateRef);
            }
            $invoices = $this->CI->mdl_invoices->getQuickbooksData(['invoice_qb_id is NULL' => null, 'clients.client_qb_id IS NOT NULL' => null], 900, 0);
            if ((!is_array($invoices) || empty($invoices)) && $module == 'All') {
                pushJob('quickbooks/payment/exportpaymentsv3', serialize(['module' => $module, 'count' => 0, 'type' => 'invoice']));
                return TRUE;
            }
            $batch = $this->dataService->CreateNewBatch();
            $sql = 'SELECT id FROM invoices WHERE invoice_qb_id IS NULL ORDER BY id DESC LIMIT 1';
            $maxInvoiceId = $this->CI->db->query($sql)->result();
            $i = 0;
            $arrInvoices = [];
            foreach ($invoices as $invoice) {
                if (is_object($invoice)) {
                    $shipFromLead = null;
                    $invoiceId = $invoice->id;
                    $items = $this->CI->mdl_estimates->find_estimate_services($invoice->estimate_id, ['estimates_services.service_status !=' => 1]);
                    $hst = getHst($invoice->estimate_hst_disabled);
                    $itemsToQB = createServicesForInvoiceQB($items, $taxCodeRef, $invoice->estimate_hst_disabled, $this->settings['tax_rate'], $this->dataService);
                    $where = [
                        'id' => $invoiceId
                    ];
                    if (is_array($itemsToQB) && !empty($itemsToQB)) {
                        $i++;
                        $arrInvoices[] = $invoiceId;
                        $discountToQB = createDiscountForInvoiceQBv2($invoice);
                        if ($discountToQB && $invoice->estimate_hst_disabled != 2) {
                            $itemsToQB[] = $discountToQB;
                        }
                        $customerId = $invoice->client_qb_id;
                        $invoiceNO = $invoice->invoice_no;
                        if(!empty($this->settings['prefix']))
                            $invoiceNO = $this->settings['prefix'] . $invoiceNO;
                        $description = $invoice->invoice_notes;
                        if($this->settings['us'])
                            $shipFromLead = getShipAddrFromCustomer($invoice);
                        $invoiceToQB = createInvoiceForQB($customerId, $itemsToQB, $invoiceNO, $description, $invoice->date_created, $invoice->overdue_date, $hst, $this->settings['location'], $this->settings['us'],  $shipFromLead, $this->settings['stateSyncInvoiceNO'], $invoice);
                        $invoiceObject = Invoice::create($invoiceToQB);
                        $batch->AddEntity($invoiceObject, $invoiceId, "create");
                        if ($i == 30 || $maxInvoiceId[0]->id == $invoiceId) {
                            $i = 0;
                            $batch->Execute();
                            $resultError = checkBatchError($batch, $this->dataService);
                            if (!$resultError)
                                return FALSE;
                            foreach ($arrInvoices as $id) {
                                $batchItemResponse = $batch->intuitBatchItemResponses[$id];
                                if (isset($batchItemResponse->entity->Id)) {
                                    $qbId = $batchItemResponse->entity->Id;
                                    $updateData = [
                                        'invoice_qb_id' => $qbId ?? 0
                                    ];
                                    $where = [
                                        'id' => $id
                                    ];
                                    $this->CI->mdl_invoices->update_invoice($updateData, $where);
                                } elseif (isset($batchItemResponse->exception)) {
                                    pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $id, 'qbId' => '']));
                                }
                            }
                            if ($maxInvoiceId[0]->id == $invoiceId && $module == 'All') {
                                pushJob('quickbooks/payment/exportpaymentsv3', serialize(['module' => $module, 'count' => 0, 'type' => 'invoice']));
                                deleteLogsInTmp();
                                return TRUE;
                            }
                            $arrInvoices = [];
                            $batch = $this->dataService->CreateNewBatch();
                        }
                    } else {
                        $updateData = [
                            'invoice_qb_id' => 0
                        ];
                        $this->CI->mdl_invoices->update_invoice($updateData, $where);
                    }
                }
            }
            $invoices = $this->CI->mdl_invoices->getQuickbooksData(['invoice_qb_id is NULL' => null, 'clients.client_qb_id IS NOT NULL' => null], 1, 0);
            if (is_array($invoices) && !empty($invoices))
                pushJob('quickbooks/invoice/exportinvoicesv3', serialize(['module' => $module, 'count' => 0]));
            elseif ($module == 'All')
                pushJob('quickbooks/payment/exportpaymentsv3', serialize(['module' => $module, 'count' => 0, 'type' => 'invoice']));

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
