<?php

class syncpaymentindb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');

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
        if(!$this->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $operation = $payload['operation'];
            if ($operation == "Delete") {
                deletePaymentInDB($payload['qbId']);
                return TRUE;
            }
            $payment = findByIdInQB($payload['module'], $payload['qbId'], $this->dataService);
            if (!$payment)
                return FALSE;
            if (is_array($payment->Line)) {
                $this->paymentArray($payment, $operation);
            } elseif (is_object($payment->Line)) {
                $this->paymentObject($payment, $operation);
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    private function paymentArray($payment, $operation)
    {
        $paymentMethods = getPaymentMethods($this->dataService);
        foreach ($payment->Line as $lineItem) {
            $documentType = $lineItem->LinkedTxn->TxnType;
            if ($documentType == 'Invoice') {
                $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
                $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
                $totalAmt = $lineItem->Amount;
                $paymentToDB = getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods);
                $estimate = $this->CI->mdl_estimates->find_by_id($estimateId);
                if ($operation == 'Create') {
                    $paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                    make_notes($estimate->client_id, 'Quickbooks: Payment for Estimate "' . $estimate->estimate_no . '" created. Transaction ID "' . $paymentId . '". ', $type = 'system', $lead_id = NULL);
                } elseif ($operation == 'Update') {
                    $message = 'Quickbooks: Payment Transaction ID for "' . $estimate->estimate_no . '": "' . $payment->Id . '" changed:<br>';
                    $message .= updatePaymentInDB($estimateId, $payment->Id, $paymentToDB);
                    make_notes($estimate->client_id, $message, $type = 'system', $lead_id = NULL);
                }
            }
        }
    }

    private function paymentObject($payment, $operation)
    {
        $lineItem = $payment->Line;
        $documentType = $lineItem->LinkedTxn->TxnType;
        $paymentMethods = getPaymentMethods($this->dataService);
        if ($documentType == 'Invoice') {
            $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
            $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
            $totalAmt = $lineItem->Amount;
            $paymentToDB = getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods);
            $estimate = $this->CI->mdl_estimates->find_by_id($estimateId);
            if ($operation == 'Create') {
                $paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                make_notes($estimate->client_id, 'Quickbooks: Payment for Estimate "' . $estimate->estimate_no . '" created. Transaction ID "' . $paymentId . '". ', $type = 'system', $lead_id = NULL);
            } elseif ($operation == 'Update') {
                $changeMessage = updatePaymentInDB($estimateId, $payment->Id, $paymentToDB);
                $message = 'Quickbooks: Payment Transaction ID for "' . $estimate->estimate_no . '": "' . $changeMessage['id'] . '" changed:<br>';
                $message .= $changeMessage['message'];
                make_notes($estimate->client_id, $message, $type = 'system', $lead_id = NULL);
            }
        }
    }
}
