<?php

use QuickBooksOnline\API\Facades\Payment;

class syncpaymentinqb extends CI_Driver implements JobsInterface
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
        if(!$this->settings['stateInQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $paymentMethods = getPaymentMethods($this->dataService);
            if(!$paymentMethods)
                return FALSE;
            $payments = $this->CI->mdl_clients->get_payments(['payment_id' => $payload['id']]);
            if(!empty($payments[0]) && !empty($payments[0]['payment_qb_id']) || !empty($payload['qbId'])) {
                $paymentQbId = !empty($payload['qbId']) ? $payload['qbId'] : $payments[0]['payment_qb_id'];
                $payments = $this->CI->mdl_clients->get_payments(['payment_qb_id' => $paymentQbId]);
            }
            if (is_array($payments) && !empty($payments)) {
                $payment = $payments[0] ?: [];
                $paymentForQB = createPaymentForQBv2($payments, $paymentMethods, $this->dataService);
                if(!$paymentForQB)
                    return FALSE;
            }
            if ((!is_array($payments) || empty($payments)) && $payload['qbId']) {
                $thePayment = getQBEntityById('Payment', $payload['qbId'], $this->dataService);
                $qbResponse = deleteRecordInQBFromObject($thePayment, $this->dataService, $payload['id']);
                if($qbResponse === false)
                    return FALSE;
            } elseif (empty($payment['payment_qb_id']) && !empty($paymentForQB)) {
                $paymentObject = Payment::create($paymentForQB);
                $qbId = createRecordInQBFromObject($paymentObject, $this->dataService, false, false, $payment['payment_id']);
                if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbId) {
                    $updateData = [
                        'payment_qb_id' => 0
                    ];
                    $this->CI->mdl_clients->update_payment($payment['payment_id'], $updateData);
                } else {
                    $updateData = [
                        'payment_qb_id' => $qbId
                    ];
                    $this->CI->mdl_clients->update_payment($payment['payment_id'], $updateData);
                }
            } elseif (!empty($payment['payment_qb_id']) && !empty($paymentForQB)) {
                $thePayment = getQBEntityById('Payment', $payment['payment_qb_id'], $this->dataService);
                if (!$thePayment)
                    return FALSE;
                $updatePayment = Payment::update($thePayment, $paymentForQB);
                updateRecordInQBFromObject($updatePayment, $this->dataService, false, $payment['payment_id']);
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}

