<?php


use application\modules\invoices\models\Invoice;

class QBDesktopInvoice
{
    const TIMESTAMP_LOGS = 1627803572;
    public $qbHeader = [
        '!TRNS',
        'TRNSID',
        'TRNSTYPE',
        'DATE',
        'ACCNT',
        'NAME',
        'CLASS',
        'AMOUNT',
        'DOCNUM',
        'MEMO',
        'CLEAR',
        'TOPRINT',
        'NAMEISTAXABLE',
        'ADDR1',
        'ADDR3',
        'TERMS',
        'SHIPVIA',
        'SHIPDATE'
    ];

    public function __construct()
    {

    }

    public function getInvoiceToQbDesktop(array $invoice){
//        $date = DateTime::createFromFormat('Y-m-d', $invoice['date_created']);
        return [
            'TRNS',
            '',
            'INVOICE',
            $invoice['date_created'],
            'Accounts Receivable',
            !empty($invoice['estimate']) && !empty($invoice['estimate']['client']) ? $invoice['estimate']['client']['client_name'] : '',
            '',
            !empty($invoice['estimate']) && !empty($invoice['estimate']['total_with_tax']) ? $invoice['estimate']['total_with_tax'] : '',
            $invoice['invoice_no'],
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];
    }
    public function getLogsDataForSelect2(){
        return Invoice::groupBy('invoice_qb_id')->where('invoice_qb_id', '>', self::TIMESTAMP_LOGS)
            ->whereDoesntHave(
                'client', function($query){
                $query->where('client_qb_id', self::TIMESTAMP_LOGS);
            })->get(['invoice_qb_id as id', 'invoice_qb_id as value', DB::raw("date_format(from_unixtime(invoice_qb_id), '". getFormatDhlDefaultDate() . " %l:%i %p') as text")])->toArray();
    }

    public function getLogsContent(string $timestamp){
        return Invoice::where('invoice_qb_id', $timestamp)->with([
            'payments' => function($query) use ($timestamp){
                $query->where('payment_qb_id', $timestamp);
            },
        ])->whereDoesntHave(
            'client', function($query) use ($timestamp){
            $query->where('client_qb_id', $timestamp);
        })->get()->toArray();
    }
}