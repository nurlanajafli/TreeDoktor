<?php
require_once('QBBase.php');

class QBPaymentActions extends QBBase
{
    protected $module = 'Payment';

    public function __construct()
    {
        parent::__construct();
    }

    public function findByIdInQB($qbId)
    {
        $record = $this->dataService->FindById('Payment', $qbId);
        $error = $this->checkError();
        if (!$error)
            return $record;
        return FALSE;
    }

    public function test()
    {
        $this->get();
    }

    public function getPaymentMethods()
    {
        $paymentMethods = $this->getAll('PaymentMethod');
        if ($paymentMethods == 'error')
            return FALSE;
        $methods = [];
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->Name == 'Cash')
                $methods[1] = $paymentMethod->Id;
            elseif ($paymentMethod->Name == 'Credit Card')
                $methods[2] = $paymentMethod->Id;
            elseif ($paymentMethod->Name == 'Cheque')
                $methods[3] = $paymentMethod->Id;
            elseif ($paymentMethod->Name == 'Check')
                $methods[3] = $paymentMethod->Id;
            elseif ($paymentMethod->Name == 'Direct Debit')
                $methods[4] = $paymentMethod->Id;

            if($paymentMethod->Type == 'CREDIT_CARD')
                $methods['CREDIT_CARD'][] = $paymentMethod->Id;
            elseif($paymentMethod->Type == 'NON_CREDIT_CARD')
                $methods['NON_CREDIT_CARD'][] = $paymentMethod->Id;
        }
        return $methods;
    }

}