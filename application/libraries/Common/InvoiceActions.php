<?php

use application\modules\clients\models\Client;
use application\modules\clients\models\ClientLetter;
use application\modules\clients\models\StatusLog;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesBundle;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\invoices\models\Invoice;
use application\modules\invoices\models\InvoiceInterest;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\payments\models\ClientPayment;
use application\modules\payments\models\ClientPaymentProject;
use application\modules\tree_inventory\models\WorkType;
use application\modules\user\models\User;

class InvoiceActions
{
    protected $CI;
    private $_title;
    private $invoice;
    private $workorder;
    private $estimate;
    private $payment_mode;
    private $invoice_id;
    private $lead_id;

    function __construct(array $params = []) {
        $this->CI =& get_instance();
        $this->_title = SITE_NAME;
        $this->invoice_id = $params['invoice_id']??NULL;
        $this->lead_id = $params['lead_id']??NULL;

        $this->CI->load->helper('user');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_invoice_status');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->model('mdl_estimates_bundles');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_invoice_status');
        $this->CI->load->library('Common/EstimateActions');


        if($this->invoice_id)
            $this->invoice = $this->CI->mdl_invoices->find_by_id($this->invoice_id);
        if($this->lead_id )
            $this->invoice = $this->CI->mdl_invoices->find_by_field(['estimates.lead_id' => $this->lead_id ]);

        /*if (!$this->invoice) {
            redirect('invoices/', 'refresh');
        }*/
        if($this->invoice) {
            $this->workorder = $this->CI->mdl_workorders->find_by_id($this->invoice->workorder_id);
            $estimate_data[0] = $this->CI->mdl_invoices->getEstimatedData($this->workorder->estimate_id);
            $this->estimate = $this->CI->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
        }
    }

    /**
     * @param $data
     * @return Invoice|\Illuminate\Database\Eloquent\Model
     */
    public function create($data) {
        $invoice = new Invoice();
        $invoice->fill($data);
        $invoice->save();
        return $invoice;
    }

    function tmpPDF() {
        include_once('./application/libraries/Mpdf.php');
        $this->CI->mpdf = new mPDF();
        $pdf = $this->getPDFTemplate();
        $this->CI->mpdf->WriteHTML($pdf['html']);

        if (!empty($pdf['pdf_files'])) {
            foreach ($pdf['pdf_files'] as $file) {
                $this->CI->mpdf->AddPage();
                $this->CI->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
            }
        }

        $file = sys_get_temp_dir() . '/' . $this->getPDFFileName();

        if(is_file($file))
            @unlink($file);

        if(file_exists($file))
            $file = sys_get_temp_dir() . '/' . uniqid() . '-' . $this->getPDFFileName();

        $this->CI->mpdf->Output($file, 'F');

        return $file;
    }

    function getPDFTemplate() {
        $data = $pdfs = [];
        //Set title:
        $data['title'] = $this->_title . ' - Invoices';
        $data['invoice_data'] = $this->invoice;

        //estimate services
        $estimateServicesData = $this->CI->mdl_estimates->find_estimate_services($this->workorder->estimate_id, array('estimates_services.service_status !=' => 1));
        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];
        foreach ($estimateServicesData as $key => $value){
            if($value['is_bundle']){
                $bundleRecords = $this->CI->mdl_estimates_bundles->get_many_by(['eb_bundle_id' => $value['id']]);
                $bundleRecordsForPDF = [];
                if(!empty($bundleRecords)){
                    foreach ($bundleRecords as $record){
                        foreach ($estimateServicesData as $esKey => $esValue){
                            if($record->eb_service_id == $esValue['id']){
                                $bundleRecordsForPDF[] = (object)$esValue;
                                unset($estimateServicesData[$esKey]);
                            }
                        }
                    }
                }
                $estimateServicesData[$key]['bundle_records'] = $bundleRecordsForPDF;
            }

            // add tree inventory work types
            if(isset($value['ties_id']) && !empty($value['ties_id'])){
                $estimateTreeInventoryServicesData[$key] = $value;
                $treeInventoryPriorities[] = $value['ties_priority'];
                unset($estimateServicesData[$key]);
                $workTypes = TreeInventoryEstimateServiceWorkTypes::where('tieswt_ties_id', $value['ties_id'])->with('work_type')->get()->pluck('work_type')->pluck('ip_name_short')->toArray();
                $treeInventoryWorkTypes = array_merge($treeInventoryWorkTypes, $workTypes);
                if(!empty($workTypes) && is_array($workTypes)){
                    $estimateTreeInventoryServicesData[$key]['work_types'] = implode(', ', $workTypes);
                }
                $estimateTreeInventoryServicesData[$key]['ties_priority'] = ucfirst(substr($value['ties_priority'], 0,1));
            }
        }
        if(!empty($estimateTreeInventoryServicesData)){
            if(!empty($treeInventoryWorkTypes))
                $data['work_types'] = WorkType::whereIn('ip_name_short', $treeInventoryWorkTypes)->get()->toArray();
            if(!empty($treeInventoryPriorities))
                $data['tree_inventory_priorities'] = array_unique($treeInventoryPriorities);
        }
        $data['estimate_services_data'] = $estimateServicesData;
        $data['estimate_tree_inventory_services_data'] = $estimateTreeInventoryServicesData;
        $estimate_data = $this->CI->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $this->workorder->estimate_id, 'invoices.id' => $this->invoice->id]);
        $data['estimate_data'] = $this->CI->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
        $data['payments_data'] = $this->CI->mdl_clients->get_payments(['client_payments.estimate_id' => $this->workorder->estimate_id]);

        //Discount data
        $data['discount_data'] = $this->CI->mdl_clients->get_discount(array('discounts.estimate_id' => $this->workorder->estimate_id));
        $data['invoice_interest_data'] = $this->CI->mdl_invoices->getInterestData($this->invoice->id);

        //Get client_id and retrive client's information:
        $id = $this->estimate->client_id;
        $data['client_data'] = $this->CI->mdl_clients->find_by_id($id);
        $data['client_contact'] = $this->CI->mdl_clients->get_primary_client_contact($id);

        $estClPath = 'uploads/clients_files/' . $this->estimate->client_id . '/estimates/' . $this->estimate->estimate_no . '/tmp/';
        $pdfFiles = $this->invoice->invoice_pdf_files ? json_decode($this->invoice->invoice_pdf_files) : [];
        $pictures['files'] = $pdfFiles;

        if(!$pictures['files']) $pictures['files'] = array();

        foreach($pictures['files'] as $key=>$file)
        {
            if(pathinfo($file)['extension'] != 'pdf')
                $data['estFiles'][] = $file;
            elseif(pathinfo($file)['extension'] == 'pdf')
                $pdfs[] = $file;
        }

        $file = $this->getPDFFileName();

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/invoice_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->CI->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'invoice_pdf', $data, TRUE);
        } else {
            $html = $this->CI->load->view('includes/pdf_templates/invoice_pdf', $data, TRUE);
        }

        return array('file' => $file, 'html' => $html, 'pdf_files' => $pdfs);
    }

    function getPDFFileName() {
        return  trim("Invoice " . $this->invoice->invoice_no . " - " . str_replace('/', '_', $this->estimate->lead_address)) . '.pdf';
    }

    function invoice_pdf()
    {
        $data = $this->getPDFTemplate();
        include_once('./application/libraries/Mpdf.php');
        $this->CI->mpdf = new mPDF();
        $this->CI->mpdf->WriteHTML($data['html']);
        foreach ($data['pdf_files'] as $file) {
            $this->CI->mpdf->AddPage();
            $this->CI->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
        }

        $this->CI->mpdf->Output($data['file'], 'I');
    }

    function setInvoiceId($invoice_id) {
        $this->invoice = $this->CI->mdl_invoices->find_by_id($invoice_id);
        if(!$this->invoice)
            return FALSE;
        $this->invoice_id = $invoice_id;
        $this->workorder = $this->CI->mdl_workorders->find_by_id($this->invoice->workorder_id);
        $estimate_data[0] = $this->CI->mdl_invoices->getEstimatedData($this->workorder->estimate_id);
        $this->estimate = $this->CI->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
        return TRUE;
    }

    function clear() {
        $this->invoice = NULL;
        $this->invoice_id = NULL;
        $this->lead_id = NULL;
        $this->estimate = NULL;
        $this->workorder = NULL;
    }
    
    function delete($estimateId)
    {
		$deleteInvoice = false;
		if($estimateId) {
			$deleteInvoice = $this->CI->mdl_invoices->delete_invoice($estimateId);
			return $deleteInvoice;
		} else {
			return false;
		}
	}

    public function getInvoicesByFilterEstimates(array $invoices, array $estimates) : array
    {
        foreach ($invoices as $key => $invoice){
            if(in_array($invoice['estimate_id'], $estimates) === false)
                unset($invoices[$key]);
        }
        return $invoices;
    }

    public function send_pdf_to_email()
    {
        $cc = $bcc = '';
        $invoice_id = $this->CI->input->post('invoice_id');
        if (!(int)$invoice_id) {
            return $this->CI->response(['status' => false, 'message' => 'Invoice id is not valid'], 400);
        }
        $to = $this->CI->input->post('emails');
        $from_email = $this->CI->input->post('email_from');
        if(/*($to && !filter_var($to, FILTER_VALIDATE_EMAIL)) || */($from_email && !filter_var($from_email, FILTER_VALIDATE_EMAIL))) {
            return $this->CI->response(['status' => false, 'message' => 'Invalid Email Address'], 400);
        }
        if($this->CI->input->post('cc') != null && $this->CI->input->post('cc') != ''){
            $cc = $this->CI->input->post('cc');
        }
        if($this->CI->input->post('bcc') != null && $this->CI->input->post('bcc') != ''){
            $bcc = $this->CI->input->post('bcc');
        } elseif(config_item('default_invoice_bcc')) {
            $bcc = $this->CI->config->item('default_invoice_bcc');
        }

        $data['estimate'] = Estimate::with(['invoice', 'client', 'user'])->whereHas('invoice', function($query) use ($invoice_id){
            $query->where('id', '=', (int)$invoice_id);
        })->first();

        $clientLetter = ClientLetter::where(['system_label' => 'invoice_for_tree_services'])->first();
        $brand_id = get_brand_id($data['estimate']->toArray()??[], $data['estimate']->client->toArray()??[]);
        $letter = ClientLetter::compileLetter($clientLetter, $brand_id, [
            'client'    =>  $data['estimate']->client,
            'estimate'  =>  $data['estimate'],
        ]);

        if(!$from_email && $letter->email_static_sender && $letter->email_static_sender !== '') {
            $from_email = $letter->email_static_sender;
        }
        if($this->CI->input->post('cc') === false && $letter->email_static_cc && $letter->email_static_cc !== '') {
            $cc = $letter->email_static_cc;
        }
        if($this->CI->input->post('bcc') === false && $letter->email_static_bcc && $letter->email_static_bcc !== '') {
            $bcc = $letter->email_static_bcc;
        }

        if (!$data['estimate']->invoice) {
            return $this->CI->response(['status' => false, 'message' => 'Invoice id is not defined'], 400);
        }

        if(is_array($to)){
            foreach ($to as $value){
                pushJob('invoices/sendinvoice', [
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'from' => $from_email,
                    'data' => $data,
                    'to' => $value,
                    'body' => $letter->email_template_text,
                    'subject' => $letter->email_template_title,
                    'invoice_id' => $invoice_id,
                    'invoice_data' => $data['estimate']->invoice,
                    'estimate_data' => $data['estimate'],
                ]);
            }
            $to = implode(', ', $to);
        } else {
            $check = check_receive_email($data['estimate']->invoice->client_id, $to);

            if($check['status'] != 'ok') {
                return $this->CI->response(['status' => false, 'message' => $check['message']], 400);
            }

            pushJob('invoices/sendinvoice', [
                'cc' => $cc,
                'bcc' => $bcc,
                'from' => $from_email,
                'data' => $data,
                'to' => $to,
                'body' => $letter->email_template_text,
                'subject' => $letter->email_template_title,
                'invoice_id' => $invoice_id,
                'invoice_data' => $data['estimate']->invoice,
                'estimate_data' => $data['estimate'],
            ]);
        }

        if(config_item('messenger') && $this->CI->input->post('sent_sms')){
            $msg = $this->compileSmsTemplate([
                '[EMAIL]' => $to
            ]);
            if($msg) {
                $this->CI->messages->send($this->CI->input->post('sent_sms'), $msg);
            }
        }

        //create a new job for synchronization in QB
        pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice_id, 'qbId' => $data['estimate']->invoice->invoice_qb_id]));

        return $this->CI->response(['status' => true, 'message' => 'Success'], 200);

    }

    public function compileSmsTemplate($additionalVars = []) {

        if(!($this->estimate) || !($this->invoice)) {
            return false;
        }

        $smsTpl = \application\modules\messaging\models\SmsTpl::find(5);

        if(!$smsTpl) {
            return false;
        }

        $brand_id = get_brand_id((array)$this->estimate, (array)$this->estimate);

        $originalVars = [
            '[CCLINK]' => isset($this->estimate->estimate_id) ? config_item('payment_link') . 'payments/' . md5($this->estimate->estimate_no . $this->estimate->client_id) : '',
            '[INVOICE_LINK]' => isset($this->invoice->invoice_no) ? config_item('payment_link') . 'payments/invoice/' . md5($this->invoice->invoice_no . $this->invoice->client_id) : '',
            '[ESTIMATE_ID]' => isset($this->estimate->estimate_id) ? $this->estimate->estimate_id : '',
            '[ESTIMATE_NO]' => isset($this->estimate->estimate_no) ? $this->estimate->estimate_no : '',
            '[INVOICE_NO]' => isset($this->invoice->invoice_no) ? $this->invoice->invoice_no : '-',
            '[DATE]' => isset($this->invoice->date_created) ? getDateTimeWithTimestamp(strtotime($this->invoice->date_created), false) : '-',
            '[NO]' => isset($this->invoice->invoice_no) ? $this->invoice->invoice_no : '-',
            '[NAME]' => isset($this->invoice->cc_name) ? $this->invoice->cc_name : $this->invoice->client_name,
            '[CLIENT_NAME]' => isset($this->invoice->cc_name) ? $this->invoice->cc_name : $this->invoice->client_name,
            '[PHONE]' => isset($this->invoice->cc_phone) ? $this->invoice->cc_phone : '-',
            '[CLIENT_PHONE]' => isset($this->invoice->cc_phone) ? $this->invoice->cc_phone : '-',
            '[EMAIL]' => isset($this->invoice->cc_email) ? $this->invoice->cc_email : '-',
            '[CLIENT_EMAIL]' => isset($this->invoice->cc_email) ? $this->invoice->cc_email : '-',
            '[ADDRESS]' => (isset($this->invoice->lead_address)) ? $this->invoice->lead_address : '-',
            '[JOB_ADDRESS]' => (isset($this->invoice->lead_address)) ? $this->invoice->lead_address : '-',
            '[AMOUNT]' => isset($this->invoice->total_due) ? money($this->invoice->total_due) : '[AMOUNT]',
            '[COMPANY_NAME]' => (brand_name($brand_id))?brand_name($brand_id):$this->CI->config->item('company_name_short'),
            '[COMPANY_EMAIL]' => (brand_email($brand_id))?brand_email($brand_id):$this->CI->config->item('account_email_address'),
            '[COMPANY_PHONE]' => (brand_phone($brand_id))?brand_phone($brand_id):$this->CI->config->item('office_phone_mask'),
            '[COMPANY_ADDRESS]' => brand_address($brand_id,$this->CI->config->item('office_address') . ', ' . $this->CI->config->item('office_city')),
            '[COMPANY_BILLING_NAME]' => (brand_name($brand_id))?brand_name($brand_id):$this->CI->config->item('company_name_long'),
            '[COMPANY_WEBSITE]' => $this->CI->config->item('company_site')
        ];

        foreach ($additionalVars as $key => $val) {
            $originalVars[$key] = $val;
        }
        
        $compiledText = trim(str_replace(array_keys($originalVars), array_values($originalVars), $smsTpl->sms_text));

        return $compiledText ?: false;
    }

    /**
     * @param Invoice $invoice
     * @param $invoice_status_id
     * @param null $amount
     * @param null $method
     * @param $credit_card_id
     * @param $payment_type
     * @return array|bool
     */
    public function setStatus(Invoice $invoice, $invoice_status_id, $amount = null, $method = null, $credit_card_id, $payment_type)
    {
        $preTotal = (new Estimate)->totalEstimateBalance($invoice->getAttribute(Invoice::ATTR_ESTIMATE_ID))->first()->total_due;

        if($invoice_status_id == $this->getPaidInvoiceStatus() && $invoice_status_id != $invoice->getAttribute(Invoice::ATTR_IN_STATUS)) {
            return $this->pay($invoice, $amount, $method, $preTotal, $credit_card_id, $payment_type);
        }

        $total = (new Estimate)->totalEstimateBalance($invoice->getAttribute(Invoice::ATTR_ESTIMATE_ID))->first()->total_due;

        $changeStatusResult = $this->change_invoice_status($invoice, $invoice_status_id, $total);
        if($changeStatusResult) {
            return ['status' => 'ok', 'thanks' => 'ok'];
        }
        return $changeStatusResult;
    }

    /**
     * @param Invoice $invoice
     * @param $amount
     * @param $method
     * @param $preTotal
     * @param $credit_card_id
     * @param $payment_type
     * @return array
     */
    protected function pay(Invoice $invoice, $amount, $method, $preTotal, $credit_card_id, $payment_type)
    {
        $amount = getAmount($amount);
        $amount = floatval($amount);

        if ((!$amount || $amount == "") && (($preTotal - $amount) > 0)) {
            //todo:: look for mashine error
            return ['status' => false, 'message' => 'Amount Is Required', 'httpCode' => 400];
        }

        if (($preTotal - $amount) > 0) {
            return ['status' => false, 'message' => 'Minimal payment amount is '.money($preTotal), 'httpCode' => 400];
        }
        if (!$method || $method == "") {
            return ['status' => false, 'message' => 'Incorrect payment method', 'httpCode' => 400];
        }

        $fee_percent = 0;
        $fee = 0;

        if ($method == config_item('default_cc')) {
            if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                return ['status' => false, 'message' => 'Maximum payment amount '.money(_CC_MAX_PAYMENT), 'httpCode' => 400];
            }

            if (is_null($credit_card_id)) {
                return ['status' => false, 'message' => 'Payment card is not selected', 'httpCode' => 400];
            }

            $fee_percent = round((float) config_item('cc_extra_fee'), 2);
            if ($fee_percent > 0) {
                $fee = round($amount * ($fee_percent / 100), 2);
                $amount += $fee;
            }
        }

        $this->CI->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');

        if (!empty($method)) {
            $this->payment_mode = $this->CI->arboStarProcessing->methodToText($method);
        }
        $estimate_data = $this->CI->mdl_estimates->find_by_id($invoice->getAttribute(Invoice::ATTR_ESTIMATE_ID));
        $client_data = Client::find($invoice->getAttribute(Invoice::ATTR_CLIENT_ID));
        $client_contact = $client_data->primary_contact()->first();

        $file = false;
        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $file = $this->CI->arboStarProcessing->uploadFile([
                'client_id' => $client_data->client_id,
                'estimate_id' => $estimate_data->estimate_id,
                'estimate_no' => $estimate_data->estimate_no,
                'invoice_no' => !!is_null($invoice) ? $invoice->getAttribute(Invoice::ATTR_INVOICE_NO) : null,
                'lead_id' => $estimate_data->lead_id
            ]);
        }

        $paymentData = [];
        if($method == config_item('default_cc')) {
            $paymentData = [
                'payment_profile' => $client_data->client_payment_profile_id,
                'card_id' => $credit_card_id,
            ];
        }

        $iData = [
            'client' => $client_data,
            'contact' => $client_contact,
            'estimate' => $estimate_data,
            'invoice' => $invoice,
            'type' => $payment_type,
            'payment_driver' => $client_data->client_payment_driver,
            'fee' => $fee,
            'fee_percent' => $fee_percent,
            'amount' => $amount,
            'file' => $file,
            'user_id' => $this->CI->user->id
        ];

        try {
            $this->CI->arboStarProcessing->pay($method, $iData, $paymentData);
        } catch (PaymentException $e) {
            return ['status' => 'error', 'error' => $e->getMessage(), 'trace' => $e->getTrace()];
        }

        return ['status' => 'ok', 'success' => true];
    }

    /**
     * @param Invoice $invoice
     * @param $status
     * @param $preTotal
     * @return bool
     */
    protected function change_invoice_status(Invoice $invoice, $status, $preTotal)
    {
        $oldStatus = InvoiceStatus::find($invoice->getAttribute(Invoice::ATTR_IN_STATUS));
        $newStatus = InvoiceStatus::find($status);
        $invoiceId = $invoice->getAttribute(Invoice::ATTR_ID);

        if($newStatus->getAttribute(InvoiceStatus::ATTR_COMPLETED) && $preTotal > 0) {
            return false;
        }
        $user_id = get_user_id();
        if(empty($user_id))
            $user_id = intval($this->CI->user->id);
        $statusLogModel = new StatusLog();
        $statusLogModel->fill([
            'status_user_id' => $user_id,
            'status_type' => 'invoice',
            'status_item_id' => $invoice->getAttribute(Invoice::ATTR_ID),
            'status_value' => $newStatus->getAttribute(InvoiceStatus::ATTR_ID),
            'status_date' => time()
        ])->save();

        Invoice::where(Invoice::ATTR_ID, '=', $invoiceId)->update([
            'in_status' => $status,
            'payment_mode' => $this->payment_mode ? $this->payment_mode : "",
            'link_hash' => '',
            'overpaid' => $preTotal < 0 ? 1 : null
        ]);
        //Update status code
        if ((int)$newStatus->getAttribute(InvoiceStatus::ATTR_COMPLETED)) {
            Invoice::where(Invoice::ATTR_ID, '=', $invoiceId)->update([
                'interest_rate' => 0,
            ]);
            InvoiceInterest::where(InvoiceInterest::ATTR_INVOICE_ID, '=', $invoiceId)->update([
                'nill_rate' => '1'
            ]);
        }
        //create a new job for synchronization in QB
        pushJob('quickbooks/invoice/syncinvoiceinqb', serialize([
            'id' => $invoiceId,
            'qbId' => $invoice->getAttribute(Invoice::ATTR_INVOICE_QB_ID)
        ]));

        $data['overdue_date'] = date('Y-m-d', strtotime('+' . Invoice::getInvoiceTerm($invoice->client_type ?? null) . ' days'));

        if((int)$newStatus->getAttribute(InvoiceStatus::ATTR_IS_OVERDUE)) {
            //insert interest into invoice_interest
            InvoiceInterest::where(InvoiceInterest::ATTR_INVOICE_ID, '=', $invoiceId)->update($data);
            $invoiceInterestModel = new InvoiceInterest();
            $invoiceInterestModel->fill([
                'invoice_id' => $invoice->getAttribute(Invoice::ATTR_ID),
                'overdue_date' => $data['overdue_date'],
                'rate' => INVOICE_INTEREST
            ])->save();
            $invoice->update_all_invoice_interes();
        }

        if((int)$newStatus->getAttribute(InvoiceStatus::ATTR_IS_HOLD_BACKS)) {
            Invoice::where(Invoice::ATTR_ID, '=', $invoiceId)->update($data);
        }

        $update_msg = "Status for invoice " . $invoice->invoice_no . ' was modified from ' . $oldStatus->invoice_status_name . ' to ' . $newStatus->invoice_status_name;
        $this->CI->load->model('mdl_followups');
        $fuRowPost = $this->CI->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $invoiceId, 'fu_status' => 'postponed']);
        $fuRowNew = $this->CI->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' =>$invoiceId, 'fu_status' => 'new']);

        if($fuRowNew && !empty($fuRowNew)) {
            $this->CI->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice status was changed']);
        } elseif($fuRowPost && !empty($fuRowPost)) {
            $this->CI->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice status was changed']);
        }

        make_notes($invoice->client_id, $update_msg, 'system', intval($invoice->invoice_no));

        return true;
    }

    public function getDefaultInvoiceStatus()
    {
        return element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'default'=>1]), 0);
    }

    public function getPaidInvoiceStatus()
    {
        return element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'completed'=>1]), 0);
    }

    public function getOverPaidInvoiceStatus()
    {
        return element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_overpaid'=>1]), 0);
    }

    public function getDefaultStatus(){

    }

    /**
     * @param Invoice $invoice
     */
    public function changeInvoiceStatusWhenUpdatingEstimate(Invoice $invoice){
        $estimateDue = (new Estimate)->totalEstimateBalance($invoice->estimate_id)->first()->total_due;
        if($invoice->in_status == $this->getPaidInvoiceStatus() && $estimateDue > 0)
            $this->change_invoice_status($invoice, $this->getDefaultInvoiceStatus(), $estimateDue);
        elseif ($invoice->in_status != $this->getPaidInvoiceStatus() && $estimateDue == 0)
            $this->change_invoice_status($invoice, $this->getPaidInvoiceStatus(), $estimateDue);
        elseif ($invoice->in_status != $this->getOverPaidInvoiceStatus() && $estimateDue < 0)
            $this->change_invoice_status($invoice, $this->getOverPaidInvoiceStatus(), $estimateDue);
    }

}
