<?php

class syncpaymentindbv2 extends CI_Driver implements JobsInterface
{
    private $CI;
    private $dataService;
    private $paymentDB;
    private $paymentQB;
    private $action;
    private $route;
    private $paymentId;
    private $settings;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->library('Common/PaymentActions');
        $this->CI->load->library('Common/QuickBooks/QBPaymentActions');
        $this->dataService = $this->CI->qbpaymentactions->dataService;
        $this->settings = $this->CI->qbpaymentactions->settings;
        $this->route = 'pull';
    }

    public function getPayload($data = NULL)
    {
        if (!$data || !$this->CI->qbpaymentactions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if(!$this->CI->qbpaymentactions->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $operation = $payload['operation'];
            $paymentQBid = $payload['qbId'];
            $this->paymentQB = $this->CI->qbpaymentactions->get($paymentQBid);
            if ($paymentQBid) {
                $this->paymentDB = $this->CI->mdl_clients->get_payment_by_qb_id($paymentQBid);
                if ($this->paymentDB) {
                    $this->CI->paymentactions->setPayment($this->paymentDB->payment_id);
                    $this->paymentId = $this->paymentDB->payment_id;
                }
            }
            if ($operation == 'Delete' || empty($this->paymentQB->TotalAmt)) {
                if(empty($this->paymentDB))
                    return true;
                $this->action = 'delete';
                $checkDelete = $this->CI->paymentactions->delete();
                pushJob('quickbooks/invoice/syncinvoiceindb', serialize(['module' => 'Invoice', 'qbId' => $this->paymentDB->invoice_qb_id, 'operation' => 'Update']));
                return $checkDelete;
            }

            $paymentMethods = $this->CI->qbpaymentactions->getPaymentMethods();
            if (!$this->paymentQB)
                return FALSE;
            $payments =  $this->CI->mdl_clients->get_payments(['payment_qb_id' => $paymentQBid]);
            if (is_array($this->paymentQB->Line)) {
                if(count($payments) != count($this->paymentQB->Line))
                    $this->CI->paymentactions->delete();
                foreach ($this->paymentQB->Line as $lineItem) {
                    $this->actionWithPayment($lineItem, $paymentMethods);
                    if ($lineItem->LinkedTxn->TxnType == 'Invoice')
                        pushJob('quickbooks/invoice/syncinvoiceindb', serialize(['module' => 'Invoice', 'qbId' => $lineItem->LinkedTxn->TxnId, 'operation' => 'Update']));
                }
            } elseif (is_object($this->paymentQB->Line)) {
                if(count($payments) > 1)
                    $this->CI->paymentactions->delete();
                $this->actionWithPayment($this->paymentQB->Line, $paymentMethods);
                if ($this->paymentQB->Line->LinkedTxn->TxnType == 'Invoice')
                    pushJob('quickbooks/invoice/syncinvoiceindb', serialize(['module' => 'Invoice', 'qbId' => $this->paymentQB->Line->LinkedTxn->TxnId, 'operation' => 'Update']));
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }

    private function actionWithPayment($lineItem, $paymentMethods)
    {
        $documentType = $lineItem->LinkedTxn->TxnType;
        if ($documentType == 'Invoice') {
            $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
            $totalAmt = $lineItem->Amount;
            if($totalAmt <= 0.01)
                return FALSE;
            $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
            $estimate = $this->CI->mdl_estimates->find_by_id($estimateId);
            if(!$estimateId)
            {
                $estimateId = createInvoiceInDB($qbInvoiceId, $this->dataService, $this->settings);
                if(!$estimateId)
                    return FALSE;
                $estimate = $this->CI->mdl_estimates->find_by_id($estimateId);
            }
            $paymentToDB = getPaymentToDB($this->paymentQB, $estimateId, $totalAmt, $paymentMethods);
            if (empty($this->paymentDB)) {
                $this->action = 'create';
                $this->paymentId = $this->CI->mdl_clients->insert_payment($paymentToDB);
                make_notes($estimate->client_id, 'Quickbooks: Payment for Estimate "' . $estimate->estimate_no . '" created. Transaction ID "' . $this->paymentId . '". ', $type = 'system', $lead_id = NULL);
            } else {
                $this->action = 'update';
                $changeMessage = updatePaymentInDB($estimateId, $this->paymentQB->Id, $paymentToDB);
                $this->paymentId = $changeMessage['id'];
                $message = 'Quickbooks: Payment Transaction ID for "' . $estimate->estimate_no . '": "' . $changeMessage['id'] . '" changed<br>';
                $message .= $changeMessage['message'];
                make_notes($estimate->client_id, $message, $type = 'system', $lead_id = NULL);
            }
            createQBLog('payment', $this->action, $this->route, $this->paymentId);
        }
    }
}