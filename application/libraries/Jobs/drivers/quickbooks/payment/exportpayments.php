<?php

use QuickBooksOnline\API\Facades\Payment;

class exportpayments extends CI_Driver implements JobsInterface
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
            $paymentMethods = getPaymentMethods($this->dataService);
            $payments = $this->CI->mdl_clients->get_payments(['payment_qb_id' => null], 1000, 0);
            if (!is_array($payments) || empty($payments)) {
                return TRUE;
            }
            foreach ($payments as $payment) {
                $paymentForQB = createPaymentForQB($payment, $paymentMethods, $this->dataService);
                $paymentObject = Payment::create($paymentForQB);
                $qbId = createRecordInQBFromObject($paymentObject, $this->dataService,false, false, $payment['payment_id']);
                if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbId) {
                    pushJob('quickbooks/payment/syncpaymentinqb', serialize(['id' => $payment['payment_id'], 'qbId' => '']));
                } else {
                    $updateData = [
                        'payment_qb_id' => $qbId
                    ];
                    $this->CI->mdl_clients->update_payment($payment['payment_id'], $updateData);
                }

            }
            $payments = $this->CI->mdl_clients->get_payments(['payment_qb_id' => null], 1000, 0);
            if (is_array($payments) && !empty($payments))
                pushJob('quickbooks/payment/exportpayments', serialize(['module' => 'Payment', 'count' => 0]));
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
