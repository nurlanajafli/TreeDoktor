<?php

use QuickBooksOnline\API\Facades\Payment;

class exportpaymentsv2 extends CI_Driver implements JobsInterface
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
        if ($job) {
            while ($this->CI->db->where('payment_qb_id IS NULL AND payment_amount > 0')->count_all_results('client_payments')) {
                $sql = 'START TRANSACTION';
                $this->CI->db->query($sql);
                $sql = 'SELECT * FROM client_payments WHERE payment_qb_id IS NULL AND payment_amount > 0 ORDER BY payment_id ASC LIMIT 15 FOR UPDATE';
                $payments = $this->CI->db->query($sql)->result();
                if ($payments) {
                    foreach ($payments as $payment) {
                        $updateData = [
                            'payment_qb_id' => -1
                        ];
                        $this->CI->mdl_clients->update_payment($payment->payment_id, $updateData);
                    }
                }
                $sql = 'COMMIT';
                $this->CI->db->query($sql);
                $paymentMethods = getPaymentMethods($this->dataService);
                if (!$paymentMethods)
                    return FALSE;
                foreach ($payments as $payment) {
                    $payment = (array)$payment;
                    $estimate = $this->CI->mdl_estimates->find_by_id($payment['estimate_id']);
                    $payment['client_id'] = $estimate->client_id;
                    $paymentForQB = createPaymentForQB($payment, $paymentMethods, $this->dataService);
                    $paymentObject = Payment::create($paymentForQB);
                    $qbId = createRecordInQBFromObject($paymentObject, $this->dataService, false, false, $payment['payment_id']);
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
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
