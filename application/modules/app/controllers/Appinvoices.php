<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\administration\models\Followups;
use application\modules\clients\models\Client;
use application\modules\estimates\models\Estimate;
use application\modules\invoices\models\InvoiceInterest;
use application\modules\messaging\models\SmsTpl;
use application\modules\workorders\models\Workorder;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\invoices\models\Invoice;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\clients\models\StatusLog;

class Appinvoices extends APP_Controller
{

    /**
     * Appinvoices constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->load->library('Messages/Messages');
    }

    /**
     * @param int $page
     * @param int $limit
     */
    public function get($page = 1, $limit = 20)
    {
        $filters = [];
        $limit = intval($limit);
        $page = intval($page);

        if (isset($_POST['filters']) && !empty($_POST['filters'])) {
            $filters = $_POST['filters'];
            if (isset($filters['search'])) {
                $checkPhone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', trim($filters['search']));
                if($checkPhone) {
                    $filters['search'] = numberFrom($filters['search']);
                }
            }
        }

        $invoices = Invoice::getInvoices($page, $filters, $limit);

        if ($invoices !== false) {
            return $this->response($invoices);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error getting a list of invoices'
            ], 400);
        }
    }

    /**
     * @param null $id
     */
    public function fetch($id)
    {
        $invoice = Invoice::find($id);
        if ($id == null || is_null($invoice)) {
            $this->response([
                'status' => false,
                'message' => 'Wrong ID provided'
            ], 400);
        }
        $invoice = Invoice::getInvoice($id);

        $this->response([
            'status' => true,
            'data' => $invoice
        ], 200);
    }

    /**
     * Update status
     */
    public function update_status()
    {
        $invoice_id = $this->input->post('invoice_id');
        $invoice_status_id = $this->input->post('invoice_status_id');
        $amount = $this->input->post('amount')??null;
        $method = $this->input->post('method')??null;
        $card_id = $this->input->post('card_id')??null;
        $payment_type = $this->input->post('payment_type') ?: 'invoice';

        $invoice = Invoice::find($invoice_id);

        if ($invoice_id == null || is_null($invoice)) {
            //todo:: look for mashine error
            return $this->response(['status' => false, 'message' => 'Wrong ID provided'], 400);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name'])) {
                //todo:: look for mashine error
                return $this->response(['status' => false, 'message' => 'File must be image or PDF'], 400);
            }
        }

        $this->load->library('Common/InvoiceActions', ['invoice_id' => $invoice_id]);
        $result = $this->invoiceactions->setStatus(
            $invoice, $invoice_status_id, $amount,
            $method, $card_id, $payment_type
        );

        if (isset($result['status']) && $result['status'] === FALSE) {
            return $this->response(['status' => $result['status'], 'message' => $result['message']], $result['httpCode']);
        }
        return $this->response(['status' => $result['status'], 'thanks' => $result['status']]);
    }

    /**
     * @param null $id
     */
    public function showPdf($id = null)
    {
        if (is_null($id) || empty($id)) {
            return $this->response([
                'status' => false,
                'message' => 'Wrong ID provided'
            ], 400);
        }

        $this->load->library('Common/InvoiceActions', ['invoice_id' => $id]);
        $result = $this->invoiceactions->invoice_pdf();
        if (!$result) {
            return $this->response([
                'status' => false,
                'message' => 'Invoice pdf not found'
            ], 400);
        }
    }

    /**
     * @return mixed
     */
    public function send_pdf_to_email()
    {
        $invoice_id = $this->input->post('invoice_id')??null;
        $this->load->library('Common/InvoiceActions');
        $this->invoiceactions->setInvoiceId($invoice_id);

        return $this->invoiceactions->send_pdf_to_email();
    }
}
