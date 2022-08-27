<?php

use QuickBooksOnline\API\Facades\Invoice;

class exportinterestinqb extends CI_Driver implements JobsInterface
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
            if (isset($payload['invoiceId']))
                $invoicesInterest = $this->CI->mdl_invoices->get_invoice_interes(['invoices.id' => $payload['invoiceId']]);
            else
                $invoicesInterest = $this->CI->mdl_invoices->get_invoice_interes(['sync_qb' => 0]);
            $taxesInQB = query('TaxRate', $this->dataService);
            if (!$taxesInQB)
                return FALSE;
            $taxRateRef = getTaxRateRef($this->settings['tax_rate'], $taxesInQB);
            $taxesInQB = query('TaxCode', $this->dataService);
            $taxCodeRef = getTaxCodeRef($taxesInQB, $taxRateRef);
//            $interestItemDBId = $this->settings['interest'];
            $interestItemDBId = createOrUpdateInterestItem($this->dataService);
            foreach ($invoicesInterest as $invoiceInterest) {
                $invoice = $this->CI->mdl_invoices->find_by_id($invoiceInterest->invoice_id);
                if ($invoice)
                    $interestData = $this->CI->mdl_invoices->getInterestData($invoiceInterest->invoice_id);
                else
                    continue;
                $discount = 0;
                $discountPercents = null;
                $estimateId = $invoiceInterest->estimate_id;
                $discountData = $this->CI->mdl_clients->get_discount(array('discounts.estimate_id' => $estimateId));
                $estimateData = $this->CI->mdl_estimates->find_by_id($estimateId);
                $paymentsData = $this->CI->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimateId));
                $items = $this->CI->mdl_estimates->find_estimate_services($estimateId, ['estimates_services.service_status' => 2]);
                $servicesAmount = getServicesAmount($items);
                $hst = getHst($estimateData->estimate_hst_disabled);

                if ($discountData && !empty($discountData)) {
                    $discountPercents = $discountData['discount_percents'];
                    $discountSum = ($estimateData->estimate_hst_disabled == 2) ? $servicesAmount / config_item('tax_rate') : $servicesAmount;
                    $discount = $discountPercents ? round($discountSum * $discountData['discount_amount'] / 100, 2) : $discountData['discount_amount'];
                }
                if ($estimateData->estimate_hst_disabled == 2) {
                    $discount = 0;
                    $discountPercents = null;
                }
//                $overdue_amt = $servicesAmount;
                $overdueAmt = $servicesAmount - $discount;
                $servicesAmount = $overdueAmt;
                if ($invoice->interest_status != 'Yes')
                    foreach ($interestData as $interestKey => $interestValue) {
                        $overdueAmt = $servicesAmount - $discount;
                        $clientData = $this->CI->mdl_clients->find_by_id($interestValue->client_id);
                        $term = \application\modules\invoices\models\Invoice::getInvoiceTerm($clientData->client_type);
                        if ($paymentsData && !empty($paymentsData)) {
                            foreach ($paymentsData as $pay) {
                                if ($pay['payment_date'] < (strtotime($interestValue->overdue_date) - $term * 86400))
                                    $overdueAmt -= $pay['payment_amount'];
                            }
                        }
                        $interest = abs($interestValue->rate / 100);
                        $overue = round($overdueAmt * $interest, 2);
                        $overdueAmt += $overue;
                        $servicesAmount += $overue;
                        if ((isset($invoice_data->interest_status) && $invoice_data->interest_status == 'Yes') || (isset($estimate_data->interest_status) && $estimate_data->interest_status == 'Yes')) {
                            $overdueAmt -= $overue;
                        }
                        $items[] = createServiceForInvoiceInterestQB($overue, $discountPercents, $discountData['discount_amount'] ?? null, $interestItemDBId, $term, $interestValue->overdue_date, $interestValue->rate);
                    }

                $theInvoice = getQBEntityById('Invoice', $invoice->invoice_qb_id, $this->dataService);
                if (!$theInvoice)
                    continue;
                $itemsToQB = createServicesForInvoiceQB($items, $taxCodeRef, $estimateData->estimate_hst_disabled, $this->settings['tax_rate'], $this->dataService);
                $discount = $this->CI->mdl_clients->get_discount(['discounts.estimate_id' => $invoice->estimate_id]);
                $discountToQB = createDiscountForInvoiceQB($discount);
                if ($discountToQB && $estimateData->estimate_hst_disabled != 2) {
                    $itemsToQB[] = $discountToQB;
                }
                $taxRate = getTaxRateRefFromTaxCode($taxesInQB, $taxRateRef);
                $invoiceToQB = getInvoiceInterestToQB($itemsToQB, $servicesAmount, $taxRate, $this->settings['tax_rate'], $hst);
                if ($estimateData->estimate_hst_disabled != 0)
                    $invoiceToQB['TxnTaxDetail'] = [];
//                die(json_encode($invoiceToQB));

                $updateInvoice = Invoice::update($theInvoice, $invoiceToQB);
                $result = updateRecordInQBFromObject($updateInvoice, $this->dataService, false, $invoiceInterest->invoice_id);
                if (!$result) {
//                    die(json_encode($invoiceToQB));
                    return FALSE;
                }
                $this->updateInterest($interestData);
            }
            if (isset($payload['action']))
                pushJob('quickbooks/payment/exportpayments', serialize(['module' => 'Payment', 'count' => 0, 'type' => 'invoice']));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    public function updateInterest($updateInterest)
    {
        if (is_array($updateInterest) && !empty($updateInterest)) {
            foreach ($updateInterest as $interestKey => $interestValue)
                $this->CI->mdl_invoices->update_invoice_interst(['sync_qb' => 1], ['id' => $interestValue->id]);
        }
    }

}
