<?php

class importpayments extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
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
            $payload = $job->job_payload;
            $i = 1;
            while (true) {
                $payments = $this->dataService->FindAll($payload, $i, 500);
                $error = checkError($this->dataService);
                if (!$error) {
                    return FALSE;
                }
                if(!$payments)
                    break;
                $paymentMethods = getPaymentMethods($this->dataService);
                foreach ($payments as $payment) {
                    $i++;
                    if (is_array($payment->Line)) {
                        foreach ($payment->Line as $lineItem) {
                            $documentType = $lineItem->LinkedTxn->TxnType;
                            if ($documentType == 'Invoice') {
                                $qbId = $payment->Id;
                                $paymentsInDB = $this->CI->mdl_clients->get_payments(['payment_qb_id' => $qbId]);
                                $checkPayment = checkPaymentInDB($paymentsInDB, $lineItem->LinkedTxn->TxnId);
                                if (!$checkPayment) {
                                    $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
                                    $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
                                    if(!$estimateId)
                                        continue;
                                    $totalAmt = $lineItem->Amount;
                                    $paymentToDB = getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods);
                                    $paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                                    createQBLog('payment', 'create', 'pull', $paymentId);
                                }
                            }
                        }
                    } elseif (is_object($payment->Line)) {
                        $lineItem = $payment->Line;
                        $documentType = $lineItem->LinkedTxn->TxnType;
                        if ($documentType == 'Invoice') {
                            $qbId = $payment->Id;
                            $paymentsInDB = $this->CI->mdl_clients->get_payments(['payment_qb_id' => $qbId]);
                            $checkPayment = checkPaymentInDB($paymentsInDB, $lineItem->LinkedTxn->TxnId);
                            if (!$checkPayment) {
                                $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
                                $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
                                if(!$estimateId)
                                    continue;
                                $totalAmt = $payment->TotalAmt;
                                $paymentToDB = getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods);
                                $paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                                createQBLog('payment', 'create', 'pull', $paymentId);
                            }
                        }
                    }
                }
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
