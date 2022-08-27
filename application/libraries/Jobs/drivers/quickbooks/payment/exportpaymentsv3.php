<?php

use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\QueryFilter\QueryMessage;

class exportpaymentsv3 extends CI_Driver implements JobsInterface
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
            $payments = $this->CI->mdl_clients->get_payments(['payment_qb_id' => null], 900, 0);
            if (!is_array($payments) || empty($payments)) {
                return TRUE;
            }
            $batch = $this->dataService->CreateNewBatch();
            $sql = 'SELECT payment_id FROM client_payments WHERE payment_qb_id IS NULL AND payment_amount > 0 ORDER BY payment_date ASC LIMIT 1';
            $maxPaymentId = $this->CI->db->query($sql)->result();
            $i = 0;
            $arrPayments = [];
            foreach ($payments as $payment) {
                $paymentId = $payment['payment_id'];
                $paymentForQB = createPaymentForQB($payment, $paymentMethods, $this->dataService);
                if(!$paymentForQB)
                    continue;
                $paymentObject = Payment::create($paymentForQB);
                $arrPayments[] = $paymentId;
                $i++;
                $batch->AddEntity($paymentObject, $paymentId, "create");
                if ($i == 30 || $maxPaymentId[0]->payment_id == $paymentId) {
                    $i = 0;
                    $batch->Execute();
                    $resultError = checkBatchError($batch, $this->dataService);
                    if (!$resultError)
                        return FALSE;
                    foreach ($arrPayments as $id) {
                        $batchItemResponse = $batch->intuitBatchItemResponses[$id];
                        if (isset($batchItemResponse->entity->Id)) {
                            $qbId = $batchItemResponse->entity->Id;
                            $updateData = [
                                'payment_qb_id' => $qbId
                            ];
                            $this->CI->mdl_clients->update_payment($id, $updateData);
                        } elseif (isset($batchItemResponse->exception)) {
                            pushJob('quickbooks/payment/syncpaymentinqb', serialize(['id' => $id, 'qbId' => '']));
                        }
                    }
                    if ($maxPaymentId[0]->payment_id == $paymentId) {
                        deleteLogsInTmp();
                    }
                    $arrPayments = [];
                    $batch = $this->dataService->CreateNewBatch();
                }
            }
            $payments = $this->CI->mdl_clients->get_payments(['payment_qb_id' => null], 1000, 0);
            if (is_array($payments) && !empty($payments))
                pushJob('quickbooks/payment/exportpaymentsv3', serialize(['module' => 'Payment', 'count' => 0]));

            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
