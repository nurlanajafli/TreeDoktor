<?php


class PaymentActions
{
    protected $CI;
    protected $payment;

    function __construct($paymentId = NULL)
    {
        $this->CI =& get_instance();

        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');

        if ($paymentId) {
            $this->payment = $this->getPaymentById($paymentId);
        }
    }

    public function setPayment($paymentId)
    {
        $this->payment = $this->getPaymentById($paymentId);
        if ($this->payment)
            return TRUE;
        return FALSE;
    }

    function getPayment()
    {
        if ($this->payment)
            return $this->payment;
        return FALSE;
    }

    public function delete()
    {
        if ($this->payment) {
            $this->CI->mdl_clients->delete_payment($this->payment->payment_id);
            return TRUE;
        }
        return FALSE;
    }

    private function getPaymentById($paymentId)
    {
        $payment = $this->CI->mdl_clients->get_payments(['payment_id' => $paymentId]);
        if ($payment) {
            return (object)$payment[0];
        }
        return FALSE;
    }

}