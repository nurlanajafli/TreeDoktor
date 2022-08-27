<?php
require_once('QBBase.php');

class QBInvoiceActions extends QBBase
{
    protected $module = 'Invoice';
    protected $invoice;

    public function __construct($invoiceQBid = null)
    {
        parent::__construct();
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_clients');
        if ($invoiceQBid) {
            $this->invoice = $this->get($invoiceQBid);
            if (!$this->invoice)
                return FALSE;
        }
    }
}