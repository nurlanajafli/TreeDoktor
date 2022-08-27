<?php

use application\modules\payments\models\ClientPayment;

class QBDesktopPayment
{
    const TIMESTAMP_LOGS = 1627803572;
    public $qbHeader = [
        '!SPL',
        'SPLID',
        'TRNSTYPE',
        'DATE',
        'ACCNT',
        'NAME',
        'CLASS',
        'AMOUNT',
        'DOCNUM'
    ];

    public function __construct()
    {

    }

    public function getTRNSToQbDesktop(array $payment)
    {
        if(empty($payment))
            return [];
        $date = new DateTime();
        $date->setTimestamp($payment['payment_date']);
        return [
            'TRNS',
            '',
            'PAYMENT',
            $date->format('Y-m-d'),
            'Undeposited Funds',
            !empty($payment['estimates']) && !empty($payment['estimates']['client']) ? $payment['estimates']['client']['client_name'] : '',
            '',
            $payment['payment_amount'],
            !empty($payment['estimates']) && !empty($payment['estimates']['estimate_no']) ? str_replace('E', 'I', $payment['estimates']['estimate_no']) : '',
        ];
    }

    public function getPaymentToQbDesktop(array $payment)
    {
        if(empty($payment))
            return [];
        $date = new DateTime();
        $date->setTimestamp($payment['payment_date']);
        return [
            'SPL',
            '',
            'PAYMENT',
            $date->format('Y-m-d'),
            'Accounts Receivable',
            !empty($payment['estimates']) && !empty($payment['estimates']['client']) ? $payment['estimates']['client']['client_name'] : '',
            '',
            $payment['payment_amount'] * -1 ,
            !empty($payment['estimates']) && !empty($payment['estimates']['estimate_no']) ? str_replace('E', 'I', $payment['estimates']['estimate_no']) : '',
        ];
    }

    public function getLogsDataForSelect2(){
        return ClientPayment::groupBy('payment_qb_id')->where('payment_qb_id', '>', self::TIMESTAMP_LOGS)
            ->whereDoesntHave(
                'invoice', function($query){
                $query->where('invoice_qb_id', self::TIMESTAMP_LOGS);
            })->get(['payment_qb_id as id', 'payment_qb_id as value', DB::raw("date_format(from_unixtime(payment_qb_id), '". getFormatDhlDefaultDate() . " %l:%i %p') as text")])->toArray();
    }

    public function getLogsContent(string $timestamp){
        return ClientPayment::where('payment_qb_id', $timestamp)->with('invoice')->whereDoesntHave(
            'invoice', function($query) use ($timestamp){
            $query->where('invoice_qb_id', $timestamp);
        }
        )->get()->toArray();
    }

}