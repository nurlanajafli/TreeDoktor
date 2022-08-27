<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\models\PaymentTransaction;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientLetter;
class Payments extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																					 Invoices Controller;
//*************
//*******************************************************************************************************************

    function __construct()
    {
        parent::__construct();

        //Checking if user is logged in;
        if (!isUserLoggedIn() && $this->router->fetch_method() != 'index'
            && $this->router->fetch_method() != 'invoice'
            && $this->router->fetch_method() != 'estimate'
            && $this->router->fetch_method() != 'client_payment'
            && $this->router->fetch_method() != 'estimate_signature'
            && $this->router->fetch_method() != 'sign_estimate'
        ) {
            redirect('login');
        }

        $this->_title = SITE_NAME;
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_invoices', 'mdl_invoices');
        $this->load->model('mdl_estimates', 'mdl_estimates');
        $this->load->helper('utilities');
        $this->load->library('form_validation');
        $this->load->helper('card_number');
        $this->load->model('mdl_workorders', 'mdl_workorders');
        $this->load->model('mdl_clients', 'mdl_clients');
        $this->load->model('mdl_payment_files', 'payment_files');
        $this->load->library('Common/EstimateActions');

        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
    }

//*******************************************************************************************************************
//*************
//*************																					 	  Payments Index;
//*************
//*******************************************************************************************************************

    public function index($hash = null)
    {
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_workorders');

        $notFound = $this->config->item('company_site_http') . '/404';
        $type = 'invoice';

        if (!$hash) {
            redirect($notFound);
        }

        $this->session->set_userdata("_INVOICE_NO", null);
        $invoice = $this->mdl_invoices->find_by_fields_join(['MD5(CONCAT(invoice_no, client_id)) = ' => $hash],
            ['invoice_statuses', 'invoice_statuses.invoice_status_id=invoices.in_status', 'left']);

        if (!$invoice) {
            $invoice = $this->mdl_invoices->find_by_fields_join(['MD5(CONCAT(id, client_id)) = ' => $hash],
                ['invoice_statuses', 'invoice_statuses.invoice_status_id=invoices.in_status', 'left']);
        }

        if ($invoice) {
            $estimate = $this->mdl_estimates->find_by_id($invoice->estimate_id);
        } else {
            $type = 'deposit';
            $estimate = $this->mdl_estimates->find_by_fields(array("MD5(CONCAT(lead_id, '-E', client_id)) = " => $hash));

            if (!$estimate) {
                $estimate = $this->mdl_estimates->find_by_fields(array('MD5(CONCAT(estimate_no, client_id)) = ' => $hash));
            }

            if (!$estimate) {
                $workorder = $this->mdl_workorders->find_by_fields(array('MD5(CONCAT(workorder_no, client_id)) = ' => $hash));

                if (!$workorder) {
                    redirect($notFound);
                    return false;
                }
                $estimate = $this->mdl_estimates->find_by_id($workorder->estimate_id);
            }

            $invoice = $this->mdl_invoices->find_by_fields_join(['estimate_id' => $estimate->estimate_id],
                ['invoice_statuses', 'invoice_statuses.invoice_status_id=invoices.in_status', 'left']);
        }

        /*redirect(base_url('portal/' . md5($estimate->estimate_no . $estimate->client_id)));
        die;*/
        if(isset($workorder) && $workorder) {
            $estimateBalance = $this->mdl_estimates->get_completed_estimate_balance($estimate->estimate_id);
        } else {
            $estimateBalance = $this->mdl_estimates->get_total_estimate_balance($estimate->estimate_id);
        }

        $lead_data = $this->mdl_leads->find_by_id($estimate->lead_id);
        $client = Client::getWithContact($estimate->client_id);

        $data['payment_type'] = $type;
        $data['client_data'] = $client->toArray();
        $data['lead_data'] = $lead_data;
        $data['invoice'] = $invoice;
        $data['estimate'] = $estimate;
        $data['workorder'] = $workorder ?? false;
        $data['estimate_balance'] = $estimateBalance;
        $data['invoice_paid_status'] = $this->mdl_invoice_status->get_by(['completed' => 1]);

        $data['billingData'] = [
            'customer_id' => $client->client_id,
            'name' => $client->client_name,
            'address' => $client->client_address,
            'city' => $client->client_city,
            'state' => $client->client_state,
            'zip' => $client->client_zip,
            'country' => $client->client_country,
            'phone' => $client->primary_contact->cc_phone_clean ?? null,
            'email' => $client->primary_contact->cc_email ?? null
        ];

        if (config_item('processing')) {
            $data['card_form'] = $this->arboStarProcessing->getClientCardForm($data, $data['client_data']['client_payment_driver']);
        }

        $this->load->view('payment_client', $data);
    }

    public function client_payment()
    {
        $this->ajax_payment(config_item('default_cc'), ['wo_confirm_how' => 'Personally Client', 'is_client' => true]);
    }

    public function ajax_payment($method = false, $extra = false, $user_id = false)
    {
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_estimates');

        /******************VALIDATION******************/
        if (!$method) {
            if (!$method = $this->input->post('method')) {
                return $this->response([
                    'status' => 'error',
                    'errors' => ['payment_method' => 'Incorrect payment method']
                ]);
            }
        }

        if (!$this->input->post('amount')) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Amount Is Required']]);
        }

        $amount = getAmount($this->input->post('amount'));

        if (!$amount) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Incorrect Payment Amount']]);
        }

        $tips = 0;

        if ($method == config_item('default_cc')) {
            if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                return $this->response([
                    'status' => 'error',
                    'errors' => ['payment_amount' => 'Maximum Payment Amount ' . money(_CC_MAX_PAYMENT)]
                ]);
            }

            if (!$this->input->post('token') && !$this->input->post('cc_id')) {
                return $this->response([
                    'status' => 'error',
                    'error' => 'Card processing error',
                    'errors' => ['cc_select' => 'Payment card is not selected']
                ]);
            }


            if ($this->input->post('token') && !$this->input->post('crd_name')) {
                return $this->response([
                    'status' => 'error',
                    'error' => 'Card processing error',
                    'errors' => ['crd_name' => 'Card Holder Name Is Required']
                ]);
            }

            if ($this->input->post('tips')) {
                $tips = (float) getAmount($this->input->post('tips'));
            }
        }

        if (!$this->input->post('estimate_id') && !$this->input->post('invoice_id') && !$this->input->post('workorder_id')) {
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name'])) {
                return $this->response(['status' => 'error', 'error' => 'File must be image or PDF']);
            }
        }

        /******************VALIDATION******************/

        $estimate_id = $this->input->post('estimate_id');
        $workorder_id = $this->input->post('workorder_id');
        $workorder_data = false;

        if($workorder_id) {
            $workorder_data = $this->mdl_workorders->find_by_id($workorder_id);
        }

        $invoice_data = $estimate_id ? $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $estimate_id]) : false;


        if (!$estimate_id && !$invoice_data && !$workorder_data) {
            $invoice_id = $this->input->post('invoice_id');
            if (!$invoice_id) {
                return $this->response(['status' => 'error', 'msg' => 'Incorrect Request']);
            }
            $invoice_data = $this->mdl_invoices->find_by_id($invoice_id);
            if (!$invoice_data) {
                return $this->response(['status' => 'error', 'msg' => 'Incorrect Invoice']);
            }
            $estimate_id = $invoice_data->estimate_id;
        } elseif($workorder_data) {
            $estimate_id = $workorder_data->estimate_id;
        } elseif ($invoice_data) {
            $estimate_id = $invoice_data->estimate_id;
        }

        $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);

        if (!$estimate_data) {
            return $this->response(['status' => 'error', 'error' => 'Incorrect Estimate']);
        }

        $client_data = Client::find($estimate_data->client_id);
        $client_contact = $client_data->primary_contact()->first();

        $fee_percent = 0;
        $fee = 0;

        $paymentData = [];

        if ($method == config_item('default_cc')) {
            if (isset($extra['is_client']) && $extra['is_client']) {
                if ($invoice_data && $invoice_data->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT) {
                    return $this->response(['status' => 'error', 'error' => 'Credit Card payment limit is full!']);
                } elseif (!$invoice_data && $estimate_data->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT) {
                    return $this->response(['status' => 'error', 'error' => 'Credit Card payment limit is full!']);
                }
            }

            if ($this->input->post('cc_id')) {
                $paymentData = array(
                    'payment_profile' => $client_data->client_payment_profile_id,
                    'card_id' => $this->input->post('cc_id'),
                );
            } elseif ($this->input->post('token') && $this->input->post('crd_name')) {
                $paymentData = array(
                    'token' => $this->input->post('token'),
                    'name' => $this->input->post('crd_name'),
                    'additional' => $this->input->post('additional')
                );
            } else {
                return $this->response(['status' => 'error', 'error' => 'Incorrect Credit Card data']);
            }

            $fee_percent = round((float) config_item('cc_extra_fee'), 2);
            if ($fee_percent > 0) {
                $fee = round($amount * ($fee_percent / 100), 2);
                $amount += $fee;
            }
        }

        $file = false;
        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $file = $this->arboStarProcessing->uploadFile([
                'client_id' => $client_data->client_id,
                'estimate_id' => $estimate_data->estimate_id,
                'estimate_no' => $estimate_data->estimate_no,
                'invoice_no' => !empty($invoice_data) ? $invoice_data->invoice_no : null,
                'lead_id' => $estimate_data->lead_id
            ]);
            if(!$file && !empty($this->arboStarProcessing->uploadFileError))
                return $this->response(['status' => 'error', 'error' => $this->arboStarProcessing->uploadFileError]);
        }
        if (!$user_id) {
            $user_id = $this->session->userdata['user_id'] ?? 0;
        }

        $iData = [
            'client' => $client_data,
            'contact' => $client_contact,
            'estimate' => $estimate_data,
            'workorder' => $workorder_data,
            'invoice' => $invoice_data,
            'type' => $this->input->post('type') ?: 'deposit',
            'payment_driver' => $client_data->client_payment_driver,
            'amount' => $amount,
            'fee' => $fee,
            'fee_percent' => $fee_percent,
            'tips' => $tips,
            'file' => $file,
            'user_id' => $user_id,
            'date' => $this->input->post('payment_date')
                ? \Carbon\Carbon::createFromTimeString($this->input->post('payment_date'))->timestamp
                : \Carbon\Carbon::now()->timestamp,
            'notes' => $this->input->post('payment_notes'),
        ];



        if ($extra) {
            $iData['extra'] = $extra;
        }

        try {
            $result = $this->arboStarProcessing->pay($method, $iData, $paymentData);
        } catch (PaymentException $e) {
            return $this->response([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }

        return $this->response([
            'status' => 'ok',
            'amnt' => $result['payment_amount'],
            'file' => $result['payment_file'],
            'total' => $result['total'],
            'thanks' => $result['thanks'] ?? ''
        ]);
    }

    function ajax_edit_payment()
    {
        if (!isAdmin()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }
        if (!$payment_id = $this->input->post('payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }
        $this->load->model('mdl_client_payments');
        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }

        if (!$this->input->post('payment_amount')) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Amount Is Required']]);
        }

        $amount = getAmount($this->input->post('payment_amount'));
        $amount = floatval($amount);
        if (!$amount) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Incorrect Payment Amount']]);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name'])) {
                return $this->response(['status' => 'error', 'error' => 'File must be image or PDF']);
            }
        }

        $this->load->model('mdl_estimates');
        $estimate = $this->mdl_estimates->find_by_id($payment->estimate_id);

        $file = false;

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $file = $this->arboStarProcessing->uploadFile([
                'client_id' => $estimate->client_id,
                'estimate_id' => $estimate->estimate_id,
                'estimate_no' => $estimate->estimate_no,
                'invoice_no' => null,
                'lead_id' => $estimate->lead_id
            ]);
        }

        if ($this->arboStarProcessing->editPayment([
            'payment_data' => $payment,
            'estimate_data' => $estimate,
            'file' => $file,
            'amount' => $amount
        ], $this->input->post())) {
            return $this->response(['status' => 'ok'], 200);
        }

        return $this->response(['status' => 'error', 'error' => 'fail'], 200);
    }

    function ajax_refund_payment()
    {
        if (!isAdmin()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }

        if (!$payment_id = $this->input->post('refund_payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }

        if (!$this->input->post('refund_payment_amount')
            || !$amount = getAmount($this->input->post('refund_payment_amount')))
        {
            return $this->response([
                'status' => 'error',
                'errors' => ['refund_payment_amount' => 'Incorrect Refund Amount']
            ], 200);
        }

        if (!$pwd = $this->input->post('refund_password')) {
            return $this->response(['status' => 'error', 'errors' => ['refund_password' => 'Empty password']], 200);
        }

        $userId = $this->session->userdata['user_id'];
        $this->load->model('mdl_user');

        if ($this->mdl_user->get_user(null, ['id' => $userId, 'password' => md5($pwd)])->num_rows() == 0) {
            return $this->response(['status' => 'error', 'errors' => ['refund_password' => 'Invalid password']], 200);
        }

        $this->load->model('mdl_client_payments');

        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }

        if ($payment->payment_method_int != config_item('default_cc')) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed refund non CC payments!'], 200);
        }

        $fee = false;
        if ($this->input->post('refund_payment_fee') && $amount === (float) $payment->payment_amount) {
            $fee = floatval($payment->payment_fee);
        }

        $this->load->model('mdl_estimates');
        $estimate = $this->mdl_estimates->find_by_id($payment->estimate_id);

        try {
            $refunded = $this->arboStarProcessing->refundClientPayment([
                'payment_data' => $payment,
                'estimate_data' => $estimate,
                'amount' => abs($amount),
                'fee' => abs($fee)
            ]);

            if ($refunded) {
                return $this->response(['status' => 'ok'], 200);
            }
        } catch (Exception $e) {
            return $this->response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }

        $this->response(['status' => 'error', 'error' => 'Fail'], 200);
    }

    function invoice($hash = null)
    {
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_estimates');

        $notFound = $this->config->item('company_site_http') . '/404';

        if (!$hash) {
            redirect($notFound);
        }

        $invoice = $this->mdl_invoices->find_by_field(array("MD5(CONCAT(estimates.lead_id, '-I', estimates.client_id)) = " => $hash));

        if (!$invoice) {
            $invoice = $this->mdl_invoices->find_by_fields(array('MD5(CONCAT(invoice_no, client_id)) = ' => $hash));
        }

        if (!$invoice) {
            $invoice = $this->mdl_invoices->find_by_fields(array('MD5(CONCAT(id, client_id)) = ' => $hash));
        }

        if (!$invoice) {
            $workorder = $this->mdl_workorders->find_by_fields(array('MD5(CONCAT(workorder_no, client_id)) = ' => $hash));
            if(!$workorder) {
                redirect($notFound);
                return false;
            }
            $data = Modules::run('workorders/workorders/partial_invoice_generate', $workorder->id);
            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($data['html']);
            $this->mpdf->Output($data['file'], 'I');
        } else {
            $data = Modules::run('invoices/invoices/invoice_pdf_generate', $invoice->id);
            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($data['html']);
            $this->mpdf->Output($data['file'], 'I');
        }
    }


    function estimate($hash = null)
    {
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_estimates');

        $notFound = $this->config->item('company_site_http') . '/404';

        if (!$hash) {
            redirect($notFound);
        }

        $estimate = $this->mdl_estimates->find_by_fields(array("MD5(CONCAT(lead_id, '-E', client_id)) = " => $hash));

        if (!$estimate) {
            $estimate = $this->mdl_estimates->find_by_fields(array('MD5(CONCAT(estimate_no, client_id)) = ' => $hash));
        }

        if (!$estimate) {
            redirect($notFound, 'refresh');
        } else {
            $data = Modules::run('estimates/estimates/estimate_pdf_generate', $estimate->estimate_id);

            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($data['html']);

            foreach ($data['files'] as $file) {
                if(pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                    $this->mpdf->AddPage('L');
                    $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
                }
            }

            $this->mpdf->Output($data['file'], 'I');
        }
    }

    /**
     *    function - to make save payments
     * @deprecated NOT USED!!
     */
    function save_payment($paymentData = array(), $data = array())
    {
        die('DEPRECATED!');
        $estimate_data = $this->mdl_estimates->find_by_id($data['estimate_id']);

        $datai = array();

        $datai["client_id"] = $data['client_id'];
        $datai["estimate_id"] = $data['estimate_id'];
        if (isset($data['invoice_id'])) {
            $datai["invoice_id"] = $data['invoice_id'];
        }
        $datai["transaction_id"] = $paymentData['id'];
        $datai["transaction_msg"] = $paymentData['message'];
        $datai["amount_paid"] = $paymentData['amount'];
        $datai["transaction_approve"] = $paymentData['approved'];
        $datai["transaction_order_no"] = $paymentData['order_number'];
        $datai["transaction_date"] = $paymentData['created'];

        $clientPayment = false;

        if ($datai["transaction_approve"] == 1) {

            $this->load->model("mdl_payments", "payments");
            $res = $this->payments->insert($datai);

            $pdata = array();

            /***SAVE CLIENT PAYMENT***/
            $path = PAYMENT_FILES_PATH . $datai["client_id"] . '/' . $estimate_data->estimate_no . '/';

            $file_no = 1;
            $files = bucketScanDir($path);
            if (!empty($files) && $files) {
                sort($files, SORT_NATURAL);
                preg_match('/payment_([0-9]{1,})/is', $files[count($files) - 1], $num);
                $file_no = isset($num[1]) ? ($num[1] + 1) : 1;
            }
            $uploadFilename = 'payment_' . $file_no . '.pdf';

            $clientPayment['estimate_id'] = $datai['estimate_id'];
            $clientPayment['payment_method'] = 'cc';
            $clientPayment['payment_method_int'] = 2;
            $clientPayment['payment_trans_id'] = $datai["transaction_id"];
            $clientPayment['payment_type'] = isset($data['type']) ? $data['type'] : false;
            $clientPayment['payment_type'] = $clientPayment['payment_type'] ? $clientPayment['payment_type'] : $this->input->post('type');
            $clientPayment['payment_type'] = $clientPayment['payment_type'] ? $clientPayment['payment_type'] : 'deposit';
            $clientPayment['payment_date'] = time();
            $clientPayment['payment_amount'] = $datai['amount_paid'];
            $clientPayment['payment_file'] = $uploadFilename;
            $clientPayment['payment_author'] = isset($this->session->userdata['user_id']) ? $this->session->userdata['user_id'] : 0;
            $this->mdl_clients->insert_payment($clientPayment);
            $this->mdl_estimates->update_estimate_balance($datai['estimate_id']);//update estimate balance
            /***SAVE CLIENT PAYMENT***/


            // send notification to client - mail
            $subject = _PAYMENT_DONE_SUBJECT;

            $body = "Hi " . $data['client_name'] . "!<br><br>";
            if ($this->session->userdata("_INVOICE_NO")) {
                $trans_details = "We have received your payment for invoice " . $this->session->userdata("_INVOICE_NO") . "! Please keep transaction details for your records:<br><br>";
            } else {
                $trans_details = "We have received your payment for estimate " . $estimate_data->estimate_no . "! Please keep transaction details for your records:<br><br>";
            }
            $trans_details .= "Transaction ID: " . $datai["transaction_id"] . "<br>";
            $trans_details .= "Transaction Message: " . $datai["transaction_msg"] . "<br>";
            $trans_details .= "Transaction Order No.: " . $datai["transaction_order_no"] . "<br>";
            $trans_details .= "Amount Paid: $" . $datai["amount_paid"] . "<br>";
            $trans_details .= "Transaction Date: " . $datai["transaction_date"] . "<br><br>";
            $body .= $trans_details;

            $body .= "Thank You,<br><br>";
            $body .= "<b>" . $this->config->item('default_email_from_second') . "</b>";

            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($trans_details);
            $file = $path . $uploadFilename;
            $uniq = uniqid();
            $this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniq . '.pdf', 'F');
            bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniq . '.pdf', $file, ['ContentType' => 'application/pdf']);
            @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniq . '.pdf');
            make_notes($datai["client_id"],
                'Payment File for ' . $estimate_data->estimate_no . ' <a href="' . base_url($file) . '">' . $uploadFilename . '</a>',
                'attachment', $estimate_data->lead_id);
        }

        try {
            $transData = $this->payment->getTransaction($datai["transaction_id"]);
        } catch (PaymentException $e) {
            /** todo: Catch PaymentException */
        }

        $icons = [
            'vi' => ['img' => 'visa.png', 'title' => 'VISA'],
            'mc' => ['img' => 'mc.png', 'title' => 'MasterCard'],
            'am' => ['img' => 'amex.png', 'title' => 'AMEX'],
            'nn' => ['img' => 'discover.png', 'title' => 'Discover'],
        ];
        $to = $this->session->userdata("_CLIENT_EMAIL_TO_PAY");
        $letterData['client_id'] = $datai["client_id"];
        $letterData['amount'] = $datai["amount_paid"];
        $letterData['id'] = $datai["transaction_id"];
        $letterData['date'] = $datai["transaction_date"];
        $letterData['payment'] = $datai["transaction_approve"];
        $letterData['message'] = $transData ? $transData['message'] : 'Incorrect Billing Information';
        $letterData['card'] = $transData && isset($transData['card']) && isset($transData['card']['last_four']) ? $transData['card']['last_four'] : '****';
        $letterData['auth_code'] = $transData && isset($transData['auth_code']) ? $transData['auth_code'] : null;
        $letterData['card_icon'] =
            $transData
            && isset($transData['card'])
            && isset($transData['card']['last_four'])
            && isset($icons[strtolower($this->payment->cardType($transData['card']['card_type']))])
                ? $icons[strtolower($this->payment->cardType($transData['card']['card_type']))]
                : ['img' => 'default.png', 'title' => 'CreditCard'];

        $this->load->library('email');
        $toDomain = substr(strrchr($to, "@"), 1);
        if (array_search($toDomain, $this->config->item('smtp_domains')) !== false) {
            $config = $this->config->item('smtp_mail');
        }
        $from_email = $this->config->item('account_email_address');
        $config['mailtype'] = 'html';

        $subject = 'Credit Card payment for ';

        $wasPay = 0;
        if ($datai["transaction_approve"] == 1) {
            $wasPay = $letterData['amount'];
        }
        if ($this->session->userdata("_INVOICE_NO")) {
            $invoiceData = $this->mdl_invoices->get_invoices('', '', '', '',
                ['estimates.estimate_id' => $data['estimate_id']]);
            $invoiceObj = $invoiceData->row();
            $letterData['invoice'] = $this->session->userdata("_INVOICE_NO");

            $data['total'] = $invoiceObj->total_with_hst;
            $data['due'] = round($invoiceObj->due/* - $wasPay*/, 2);
            $letterData['total'] = $data['total'];
            $letterData['due'] = $data['due'];
            $subject .= 'Invoice ' . $this->session->userdata("_INVOICE_NO");
        } else {
            $estData = $this->mdl_estimates->estimate_sum_and_hst($data['estimate_id']);
            if ($estData) {
                $letterData['total'] = $estData['total'] + $estData['hst'];
                $paymentsSum = isset($estData['payments']) ? $estData['payments'] : 0;
                $letterData['due'] = $letterData['total'] - $paymentsSum;// - $wasPay;
            }
            $letterData['estimate'] = $estimate_data->estimate_no;
            $subject .= 'Estimate ' . $estimate_data->estimate_no;
        }

        if ($datai["transaction_approve"] == 1) {
            $subject .= ' is Approved';
        } else {
            $subject .= ' is Declined';
        }

        $text = $this->load->view('payments/payment_check', $letterData, true);

        $this->email->initialize($config);

        $this->email->to($to);
        $this->email->from($from_email, $this->config->item('company_name_short'));
        $this->email->subject($subject);
        $this->email->message($text);

        //file_put_contents('uploads/test.html', $text . $to);
        $this->email->send();

        $name = uniqid();
        $note_id = make_notes($datai["client_id"], 'Sent email "' . $subject . '"', 'email', $estimate_data->lead_id, $this->email);
        $dir = 'uploads/notes_files/' . $datai["client_id"] . '/' . $note_id . '/';

        $pattern = "/<body>(.*?)<\/body>/is";
        preg_match($pattern, $text, $res);
        $note['text'] = isset($res[1]) && $res[1] ? $res[1] : $text;
        $note['from'] = $from_email;
        $note['to'] = $to;
        $note['subject'] = $subject;

        $file = bucket_write_file($dir . $name . '.html', $this->load->view('clients/note_file', $note, true),
            ['ContentType' => 'text/html']);

        return $clientPayment;
    }

    function ajax_get_card_form()
    {
        if (!$client_id = $this->input->post('client_id')) {
            return $this->response(['error' => 'No client ID'], 400);
        }

        $client = Client::getWithContact($client_id);

        if (empty($client)) {
            return $this->response(['error' => 'No client found'], 400);
        }

        if (config_item('processing')) {
            $billingData = [
                'customer_id' => $client->client_id,
                'name' => $client->client_name,
                'address' => $client->client_address,
                'city' => $client->client_city,
                'state' => $client->client_state,
                'zip' => $client->client_zip,
                'country' => $client->client_country,
                'phone' => $client->primary_contact->cc_phone_view ?? null,
                'email' => $client->primary_contact->cc_email ?? null,
                'profile_id' => $client->client_payment_profile_id
            ];

            $card_form = $this->arboStarProcessing->getCardForm(
                $billingData,
                $client->client_payment_driver
            );
        } else {
            return $this->response(['error' => 'Payments disabled'], 400);
        }

        return $this->response($card_form, 200);
    }

    function ajax_get_transaction_details()
    {
        if (!$payment_id = $this->input->post('payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }

        $this->load->model('mdl_client_payments');
        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }

        if (!$payment->payment_trans_id) {
            return $this->response(['status' => 'error', 'error' => 'Transaction not found'], 200);
        }

        if (!$transaction = PaymentTransaction::find($payment->payment_trans_id)) {
            return $this->response(['status' => 'error', 'error' => 'Transaction not found'], 200);
        }

        $transaction->payment_transaction_status = $this->arboStarProcessing->statusToText($transaction->payment_transaction_status);

        return $this->response([
            'status' => 'ok',
            'html' => $this->load->view('payments/transaction_details', ['transaction' => $transaction], true)
        ], 200);
    }

    function ajax_get_payment()
    {
        if (!isAdmin()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }
        if (!$payment_id = $this->input->post('payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }
        $this->load->model('mdl_client_payments');
        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }
//        if ($payment->payment_method_int == config_item('default_cc')) {
//            return $this->response(['status' => 'error', 'error' => 'Not allowed to edit CC payments!'], 200);
//        }
        $payment->payment_date = date(getDateFormat(), $payment->payment_date);

        $response = ['status' => 'ok', 'payment' => $payment];

        if(!empty($this->input->post('same_estimates'))){
            $this->load->model('mdl_estimates');
            $response['estimates'] = $this->mdl_estimates->get_same_client_estimates($payment->estimate_id);
        }

        return $this->response($response, 200);
    }

    function ajax_get_same_client_estimates()
    {
        if (!isAdmin()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }
        if (!$payment_id = $this->input->post('payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }
        $this->load->model('mdl_client_payments');
        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }


        return $this->response(['status' => 'ok', 'payment' => $payment], 200);
    }

    function ajax_delete_payment()
    {
        if (!isAdmin()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }
        if (!$payment_id = $this->input->post('payment_id')) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }

        $this->load->model('mdl_client_payments');
        if (!$payment = $this->mdl_client_payments->fetch($payment_id)) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }
        if ($payment->payment_method_int == config_item('default_cc')) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed to edit CC payments!'], 200);
        }

        $this->load->model('mdl_estimates');
        $estimate = $this->mdl_estimates->find_by_id($payment->estimate_id);

        if ($this->arboStarProcessing->deletePayment(['payment_data' => $payment, 'estimate_data' => $estimate])) {
            return $this->response(['status' => 'ok'], 200);
        }

        return $this->response(['status' => 'error', 'error' => 'fail'], 200);
    }

    function estimate_signature($hash){
        if(!empty($hash)) {
            $data['estimate'] = $this->mdl_estimates->find_by_fields_join(['MD5(CONCAT(estimate_id)) = ' => $hash]);
            /*redirect(base_url('portal/' . md5($data['estimate']->estimate_no . $data['estimate']->client_id)));
            die;*/
            if (!empty($data['estimate']->client_id)) {
                $data['client_data'] = $this->mdl_clients->find_by_id($data['estimate']->client_id);
                $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($data['estimate']->client_id);
                $data['lead_data'] = $this->mdl_leads->find_by_id($data['estimate']->lead_id);
                $data['hash'] = $hash;
                $this->load->view('estimate_signature', $data);
            }
        }
    }

    function sign_estimate() {
        $estimateId = $this->input->post('estimate_id');
        $estimate =  $this->mdl_estimates->find_by_fields_join(['MD5(CONCAT(estimate_id)) = ' => $estimateId], ['leads', 'estimates.lead_id = leads.lead_id'], 'left');
        $isWeb = !empty($this->input->post('is_web')) ? true : false;

        if(!$estimate) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Estimate'
            ], 200);
        }

        $this->estimateactions->setEstimateId($estimate->estimate_id);
        $this->estimateactions->setIsConfirmedWeb($isWeb);


        if($this->estimateactions->sign($this->input->post('signature'))) {
           $this->estimateactions->confirm('Signature');

            $this->estimateactions->sendConfirmedCompanyNotification();
            $this->estimateactions->sendConfirmed();
            
            return $this->response([
                'status' => TRUE,
                'message' => 'Confirmation success!'
            ], 200);
        }

        return $this->response([
            'status' => FALSE,
            'message' => 'Confirmation Error'
        ], 200);
    }

}
