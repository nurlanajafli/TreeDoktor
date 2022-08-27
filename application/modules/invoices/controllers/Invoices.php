<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\clients\models\ClientLetter;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\tree_inventory\models\WorkType;

class Invoices extends MX_Controller
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
        if (!isUserLoggedIn() && isInvoiceAccessible()) {
            redirect('login');
        }
        if (is_cl_permission_none())
            redirect(base_url());

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_invoices', 'mdl_invoices');
		$this->load->model('mdl_invoice_status');
		$this->load->model('mdl_user');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_workorders', 'mdl_workorders');
		$this->load->model('mdl_estimates', 'mdl_estimates');
		$this->load->model("mdl_payments", "payments");
		$this->load->model("mdl_payment_files", "payment_files");

		$this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
		$this->load->model('mdl_services_orm', 'mdl_services_orm');
		$this->load->model('mdl_crews_orm', 'mdl_crews_orm');
		$this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
		$this->load->model('mdl_calls');
		$this->load->model('mdl_sms_messages');
        $this->load->model('mdl_users_orm', 'mdl_users_orm');
        $this->load->model('mdl_estimates', 'mdl_estimates');
		//Load helpers
		$this->load->library('mpdf');
		$this->load->library('pagination');
		$this->load->library('googlemaps');
		
	}

//*******************************************************************************************************************
//*************
//*************																					 	  Invoices Index;
//*************
//*******************************************************************************************************************	

	public function index($type = 1, $page = 1, $estimator = NULL, $filter = 0, $csv = false)
	{
		$data['title'] = $this->_title . ' - Invoices';
		$data['menu_invoices'] = "active";
		$data['overpaid'] = NULL;
		$data['filter'] = intval($filter);
		$data['search_keyword'] = $this->input->get('q');
		$data['from'] = $this->input->get('from');
		$data['to'] = $this->input->get('to');
        $data['sortBy'] = in_array($this->input->get('sort_by'), ['invoice_no', 'date_created']) ? 'invoices.' . $this->input->get('sort_by') : 'date_created';
        $data['sortRule'] = in_array($this->input->get('sort_rule'), ['asc', 'desc']) ? $this->input->get('sort_rule') : 'desc';
        $data['queryString'] = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : NULL;

		$status_where = ['invoice_status_id'=>intval($type)];
		$data['completed_status'] = (int)element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'completed' => 1]), 0);

		if($type == 'overpaid')
			$status_where = ['completed'=>1];

		$data['status'] = $status = $this->mdl_invoice_status->get_by($status_where);

		$data['type'] = $status->invoice_status_id;
		$data['filter'] = ($status->completed && $filter)?$filter:0;
		$data['users'] = $this->mdl_user->get_usermeta(['employees.emp_field_estimator' => '1'])->result_array();
		$data['invoices_statuses'] = $this->mdl_invoice_status->order_by('priority')->get_many_by(['invoice_status_active'=>1]);

		$where = [];
		$count_where = ['invoices.overpaid'=>NULL];
		if($data['from'] && $data['from'] != null && $data['from'] != ''){
			$count_where['invoices.date_created >='] = $where['invoices.date_created >='] = $data['from'];
		}
		if($data['to'] && $data['to'] != null && $data['to'] != ''){
			$count_where['invoices.date_created <='] = $where['invoices.date_created <='] = $data['to'];
		}

		if(is_cl_permission_owner()) {
		    $estimator = request()->user()->id;
        } elseif (is_cl_permission_none()) {
		    $estimator = -1;
        }

		if(intval($estimator))
			$data['estimator'] = $count_where['estimates.user_id'] = $where['estimates.user_id'] = intval($estimator);

		$invoices_counts = $this->mdl_invoices->invoices_record_count($count_where, $data['search_keyword'], $data['filter']);
		foreach ($invoices_counts as $key => $value)
			$data['invoices_by_statuses'][$value['invoice_status_id']] = $value['total'];

		/* Over Paid Counter */
		$count_where['invoices.overpaid'] = 1;
		$count_where['invoice_statuses.is_overpaid'] = 1;
		$overpaid_counter = $this->mdl_invoices->invoices_record_count($count_where, $data['search_keyword'], $data['filter']);
		$data['invoices_by_statuses']['overpaid'] = isset($overpaid_counter[0]['total'])?$overpaid_counter[0]['total']:0;


		$config = [];
		$config["base_url"] = base_url() . "invoices/" . $data['type'];
		$config["total_rows"] = isset($data['invoices_by_statuses'][$data['type']]) ? $data['invoices_by_statuses'][$data['type']] : 0;

		if($type == 'overpaid'){
			$where['invoices.overpaid'] = $data['overpaid'] = $status->is_overpaid;
			$config["base_url"] = base_url() . "invoices/overpaid";
			$config["total_rows"] = $data['invoices_by_statuses']['overpaid'];
		}

		$config["per_page"] = 100;
		$config["uri_segment"] = 3;
		$config['use_page_numbers'] = TRUE;
        $config['enable_query_strings'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config["first_url"] = '/invoices/'.$data['type'].'/1/';

		$config['suffix'] = NULL;
		if(isset($data['estimator']))
            $config['suffix'] = '/' . $data['estimator'] . '/';
        $config['suffix'] .= '/' .$data['filter'];
		$config['first_url'] .=  $config['suffix'];

		if($data['queryString'])
			$config['suffix'] .= '?' . $data['queryString'];

		$this->pagination->initialize($config);

		$start = ($page - 1) * $config["per_page"];
		$limit = $config["per_page"];
		$data["links"] = $this->pagination->create_links();

		$where['invoices.in_status'] = $data['type'];
		if(!$csv || !isAdmin()) {
            $data['invoices'] = $this->mdl_invoices->invoices($where, $data['search_keyword'], $start, $limit, $data['filter'], $data['sortBy'], $data['sortRule']);
        } else {
		    return $this->_csv($this->mdl_invoices->invoices($where, $data['search_keyword'], false, false, $data['filter'], $data['sortBy'], $data['sortRule']));
        }
        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
		$this->load->view("index", $data);
	}

    private function _csv($data = []) {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=invoices.csv',
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $csvColumns = [
            'invoice_no' => 'Invoice №',
            'client_name' => 'Client Name',
            'client_address' => 'Address',
            'client_city' => 'City',
            'cc_name' => 'Contact Person',
            'cc_phone' => 'Phone',
            'cc_email' => 'Email',
            'estimator_name' => 'Estimator',
            'total' => 'Total For Services',
            'total_with_hst' => 'Total With Tax',
            'due' => 'Total Due',
            'date_created' => 'Date Created',
            'invoice_notes' => 'Notes',
        ];

        $csvData = [];

        foreach ($data as $k => $row) {
            foreach ($csvColumns as $key => $val) {
                $csvData[$k][$val] = $row->$key;
            }
        }

        foreach ($headers as $key => $header) {
            header("$key: $header");
        }

        array_unshift($csvData, array_keys($csvData[0]));

        $FH = fopen('php://output', 'w');
        foreach ($csvData as $key => $row) {
            fputcsv($FH, $row);
        }

        echo stream_get_contents($FH);
        fclose($FH);
    }

//*******************************************************************************************************************
//*************
//*************																					 	   Invoices Edit;
//*************
//*******************************************************************************************************************	

	public function edit($id)
	{
		if (!$id) // NB: Set to redirect to index if variable is null or not set;
			return redirect('invoices/', 'refresh');

		//Set title:
		$data['title'] = $this->_title . ' - Edit Invoice';
		$data['menu_invoices'] = "active";

		//Get invoice informations - using common function from MY_Models;
		$data['invoices_data'] = $this->mdl_invoices->find_by_id($id);
		//Get client_id and retrive client's information:
		$id = $data['invoices_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($client_id);

		// load view;
		$this->load->view("edit", $data);

	}// End Edit Workorder.

//*******************************************************************************************************************
//*************
//*************																					 	 Invoices Update;
//*************
//*******************************************************************************************************************		
	public function update_invoice()
	{

		//Get hidden variables
		$wdata['id'] = intval($this->input->post('invoice_id'));
		$client_id = intval($this->input->post('client_id'));
		$invoice_no = strip_tags($this->input->post('invoice_no'));

		//form data:
		$data['in_finished_how'] = strip_tags($this->input->post('in_finished_how'));
		$data['in_extra_note_crew'] = strip_tags($this->input->post('in_extra_note_crew'));
		$data['payment_mode'] = strip_tags($this->input->post('payment_mode'));


		if ($this->mdl_invoices->update_invoice($data, $wdata)) {
			if (make_notes($client_id, 'Inovice "' . $invoice_no . '" updated', 'system', intval($invoice_no))) {
				$mess = message('success', 'Invoice Updated!');
				$this->session->set_flashdata('user_message', $mess);

                //create a new job for synchronization in QB
                $invoice = $this->mdl_invoices->find_by_id($this->input->post('invoice_id'));
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $this->input->post('invoice_id'), 'qbId' => $invoice->invoice_qb_id]));

                $link = base_url($invoice_no);
				redirect($link);
			}
		} else {
			$mess = message('alert', 'invoice updating failed!');
			$this->session->set_flashdata('user_message', $mess);
			$link = base_url($invoice_no);
			redirect($link);
		}

	}// End. Update Workorders

//*******************************************************************************************************************
//*************
//*************																					 	Invoices Profile;
//*************
//*******************************************************************************************************************		

	public function profile($invoice_id=null, $lead_id = NULL)
	{

		if (!isset($invoice_id) && !$lead_id)//NB: Set to redirect to index if variable is null;
			redirect('invoices/', 'refresh');
		
		$this->load->helper('estimates_helper');
		$this->load->model('mdl_qa');
		$this->load->model('mdl_letter');
		$this->load->model('mdl_sms');

		//Set title:
		$data['title'] = $this->_title . ' - Invoices';
		$data['menu_invoices'] = "active";
		$data['invoice'] = TRUE;
		//Get invoices data

		if($invoice_id) {
		    $where['invoices.id'] = $invoice_id;
        }

		if($lead_id) {
            $where['estimates.lead_id'] = $lead_id;
        }

        $data['invoice_data'] = $this->mdl_invoices->find_by_field($where);
		
		if (!$data['invoice_data'])
			page_404(['message'=>'This invoice does not exist']);
		
		$invoice_id = $data['invoice_data']->id;
		
		$invoices_statuses = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
        foreach ($invoices_statuses as $key => $value) {
            $data['invoices_statuses'][$value->invoice_status_id] = $value;
            if($value->is_overpaid == 1 && $data['invoice_data']->in_status == $value->invoice_status_id) {
                $letter = ClientLetter::where('system_label', 'invoice_paid_thanks')->first();
                if(!empty($letter) && $letter)
                    $data['paid_invoice_template_id'] = $letter->email_template_id;
            }
        }


		//Get workorder informations - using common function from MY_Models;
		$workorder_id = $data['invoice_data']->workorder_id;
		$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);
		if (!$data['workorder_data'])
			show_404();
		//Get estimate informations - using common function from MY_Models;
		$estimate_id = $data['workorder_data']->estimate_id;
		
		$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimates.estimate_id' => $estimate_id))[0];
		
		$data['wo_statuses'] = $this->mdl_services_orm->get_service_status();
		$client_id = $data['estimate_data']->client_id;
		$estimate_id = $data['estimate_data']->estimate_id;
		$name = $data['estimate_data']->client_name;
		$street = $data['estimate_data']->lead_address;

        $client_tags = $this->mdl_clients->get_client_tags(array('client_id' => $client_id)); //Get client contacts
        $data['client_tags'] = array_map(function ($item){
            return ['id'=>$item['tag_id'], 'text' => $item['name']];
        }, $client_tags);

        $data['client_estimates'] = $this->mdl_estimates->get_client_estimates($client_id); //Get client estimates

		$data['client_payments'] = $this->mdl_clients->get_payments(array('clients.client_id' => $client_id)); //Get client payments

		foreach($data['client_payments'] as $payment)
			$data['payed_for_estimate'][$payment['estimate_id']] = isset($data['payed_for_estimate'][$payment['estimate_id']]) ? $data['payed_for_estimate'][$payment['estimate_id']] + $payment['payment_amount'] : $payment['payment_amount'];
		
		
		
		$data['client_papers'] = $this->mdl_clients->get_papers(['cp_client_id' => $client_id], 'cp_id DESC');
		
		$client_contact = $this->mdl_clients->get_primary_client_contact($client_id);
		
		$city = $data['estimate_data']->lead_city;
		$address = $street . "+" . $city;
			//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = '10';

		$this->googlemaps->initialize($config);
		
		$marker = array();
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();
		
		
		$data['estimate_qa'] = $this->mdl_estimates->find_estimate_qa($estimate_id);

		//estimate services
		$data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);

		$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($invoice_id);
		
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));

		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
		//Get client_id and retrive client's information:
		$client_id = $data['workorder_data']->client_id;
		$data['client_data'] = $this->mdl_clients->get_client_by_id($client_id);
		$data['client_contact'] = $client_contact;
		$data['client_contact']['address'] = str_replace(array('+', '#'), array(' '), $address);
		$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts

//        $jkey = md5($this->config->item('encryption_key') . $data['client_data']->client_id);
//		$data['client_data']->client_cc_number = $data['client_data']->client_cc_number;
		//qa
		$data['qa'] = $this->mdl_qa->find_all_with_limit(array(), '', '', 'qa_type_int', array('qa_status' => 1));

		$data['payment_files'] = $this->payment_files->get_payment_file(array("invoice_id" => $invoice_id));

        $brand_id = get_brand_id($data['estimate_data'], $data['client_data']);

        $letter = ClientLetter::where('email_template_id', '=', 7)->first();
        $data['invoice_letter_template_id'] = $letter->email_template_id;


		$letter = $this->mdl_letter->get_all(array('email_template_id' => 9));
		$data['thanks_text'] = $letter[0];
		$this->load->model('mdl_vehicles');
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));
		$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contact
		
		$data['sms'] = array();
		$data['messages'] = array();
		$sms = $this->mdl_sms->get(5);
		if($sms &&(is_object($sms) || !empty($sms)))
		{
			$data['sms'] = $sms;
			$data['messages'] = [json_decode(json_encode($sms), FALSE)];
		}
		$data['tiny'] = TRUE;

        $taxes = all_taxes();
        $data['taxText'] = $data['estimate_data']->estimate_tax_name . ' (' . round($data['estimate_data']->estimate_tax_value, 2) . '%)';
        $data['taxName'] = $data['estimate_data']->estimate_tax_name;
        $data['taxRate'] = $data['estimate_data']->estimate_tax_rate;
        $data['taxValue'] = $data['estimate_data']->estimate_tax_value;
        $checkTax = checkTaxInAllTaxes($data['taxText']);
        if(!$checkTax)
            $taxes[] = ['text' => $data['taxText'], 'id' => $data['taxText']];
        // Tax recommendation for US companies if the address has changed
        if(config_item('office_country') == 'United States of America') {
            if(!empty($data['estimate_data']->lead_tax_name) && !empty($data['estimate_data']->lead_tax_value) && $data['estimate_data']->lead_tax_value != $data['estimate_data']->estimate_tax_value){
                $data['taxRecommendation'] = round($data['estimate_data']->lead_tax_value, 2);
                $data['taxEstimate'] = round($data['estimate_data']->estimate_tax_value, 2);
                $taxText = $data['estimate_data']->lead_tax_name . ' (' . round($data['estimate_data']->lead_tax_value, 2) . '%)';
                $checkTax = checkTaxInAllTaxes($taxText);
                if(!$checkTax)
                    $taxes[] = ['text' => $taxText,'id' => $taxText];
            }
        }
        $data['allTaxes'] = $taxes;
		//Load view
		$this->load->view('profile', $data);
		
		// end else;
	}//End Profile();

	function no($no = NULL) {
		if(!$no) {
			show_404();
		}

		$this->profile(NULL, $no);
	}

	function pdf($no = NULL, $event = NULL) {
		if(!$no) {
			show_404();
		}

		$this->invoice_pdf(NULL, $no);
	}

	/*******DEPRECATE*********/
	/*public function invoices_mapper($status = null)
	{
		if (!$status)
			return redirect(base_url('invoices/invoices_mapper/1'));

		//Page Presets
		$data['title'] = $this->_title . ' - Invoices Map - ' . ucwords(str_replace('_', ' ', $status));
		$data['menu_invoices'] = "active";
		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		$where = ['invoices.in_status' => $status];

        $estimator = false;
        if(is_cl_permission_owner()) {
            $estimator = request()->user()->id;
        } elseif (is_cl_permission_none()) {
            $estimator = -1;
        }

        if(intval($estimator))
            $where['estimates.user_id'] = intval($estimator);

		//Get required workorder data:
		$invoices = $this->mdl_invoices->invoices($where);
		$data['status_name'] = $status;
		$data['statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
		if ($invoices && !empty($invoices)) {
			foreach ($invoices as $row) {

				$address = $row->client_address . " " . $row->client_city;
				//Marker Content:
				$marker_link = base_url($row->invoice_no);
				$marker_content = "<strong><a href='" . $marker_link . "' target='_blank'>" . $row->invoice_no . "<br>" . $address . "</a></strong>";
				$marker_content .= "<br>Status: " . $row->invoice_status_name;
				$marker_style = mappin_svg('#D8B44F', '&#9899;', FALSE, '#000');
				$marker = array();
				$marker['position'] = $row->latitude . ',' . $row->longitude;
				$marker['infowindow_content'] = $marker_content;
				$marker['icon'] = $marker_style;
				$this->googlemaps->add_marker($marker);
			}
		}
		$data['map'] = $this->googlemaps->create_map();

		$this->load->view('map', $data);
	}*/

//*******************************************************************************************************************
//*************
//*************
//*************																			Ajax Change Invoice Status
//*************
//*************
//*******************************************************************************************************************
    function ajax_change_invoice_status(){
        $user = $this->mdl_users_orm->get($this->session->userdata('user_id'));

        /******************VALIDATION******************/

        if (!$invoice_id = $this->input->post('invoice_id'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if (!$new_invoice_status = $this->input->post('new_invoice_status'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if (!$pre_invoice_status = $this->input->post('pre_invoice_status'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if ($new_invoice_status == $pre_invoice_status) {
            return $this->response(['status' => 'ok']);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name']))
                return $this->response(['status' => 'error', 'error' => 'File must be image or PDF']);
        }

        //$new_status_data = $this->mdl_invoice_status->get_by(['invoice_status_id' => $new_invoice_status]);
        $invoice_data = $this->mdl_invoices->find_by_id($invoice_id);

        $preTotal = $this->mdl_estimates->get_total_estimate_balance($invoice_data->estimate_id);

        if ($new_invoice_status == 4 && $preTotal > 0) {
            $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
            /******************VALIDATION******************/

            $amount = $this->input->post('payment_amount');

            if (empty($amount)) {
                return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Amount Is Required']]);
            }

            $amount = getAmount($amount);
            $amount = floatval($amount);


            if (($preTotal - $amount) > 0) {
                return $this->response([
                    'status' => 'error',
                    'errors' => ['payment_amount' => 'Minimal payment amount is '.money($preTotal)]
                ]);
            }

            $method = $this->input->post('method');

            if (empty($method)) {
                return $this->response([
                    'status' => 'error', 'errors' => ['payment_method_status' => 'Incorrect payment method']
                ]);
            }

            $fee_percent = 0;
            $fee = 0;

            if ($method == config_item('default_cc')) {
                if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                    return $this->response([
                        'status' => 'error',
                        'errors' => ['payment_amount' => 'Maximum Payment Amount '.money(_CC_MAX_PAYMENT)]
                    ]);
                }

                if (!$cc_id = $this->input->post('cc_id')) {
                    return $this->response([
                        'status' => 'error', 'error' => 'Card processing error',
                        'errors' => ['cc_select' => 'Payment card is not selected']
                    ]);
                }

                $fee_percent = round((float) config_item('cc_extra_fee'), 2);
                if ($fee_percent > 0) {
                    $fee = round($amount * ($fee_percent / 100), 2);
                    $amount += $fee;
                }
            }

            if (!empty($method)) {
                $payment_mode = $this->arboStarProcessing->methodToText($method);
            }
            /******************VALIDATION******************/

            $estimate_data = $this->mdl_estimates->find_by_id($invoice_data->estimate_id);
            $client_data = Client::find($invoice_data->client_id);
            $client_contact = $client_data->primary_contact()->first();

            $file = false;

            if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
                $file = $this->arboStarProcessing->uploadFile([
                    'client_id' => $client_data->client_id,
                    'estimate_id' => $estimate_data->estimate_id,
                    'estimate_no' => $estimate_data->estimate_no,
                    'invoice_no' => !empty($invoice_data) ? $invoice_data->invoice_no : null,
                    'lead_id' => $estimate_data->lead_id
                ]);
            }

            $paymentData = [];
            if($method == config_item('default_cc')) {
                $paymentData = [
                    'payment_profile' => $client_data->client_payment_profile_id,
                    'card_id' => $cc_id,
                ];
            }

            $iData = [
                'client' => $client_data,
                'contact' => $client_contact,
                'estimate' => $estimate_data,
                'invoice' => $invoice_data,
                'type' => $this->input->post('payment_type') ?: 'invoice',
                'payment_driver' => $client_data->client_payment_driver,
                'fee' => $fee,
                'fee_percent' => $fee_percent,
                'amount' => $amount,
                'file' => $file,
                'user_id' => $this->session->userdata['user_id']
            ];

            try {
                $result = $this->arboStarProcessing->pay($method, $iData, $paymentData);
            } catch (PaymentException $e) {
                return $this->response([
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($this->change_invoice_status([
            'invoice_id' => $invoice_id,
            'pre_invoice_status' => $pre_invoice_status,
            'new_invoice_status' => $new_invoice_status,
            'payment_mode' => $payment_mode ?? "",
        ])) {
            return $this->response(['status' => 'ok', 'thanks' => $result['thanks'] ?? '']);
        }

        return $this->response(['status' => 'error', 'error' => 'Error']);
    }

	function change_invoice_status($update_data)
	{
        $invoice_id = $update_data['invoice_id'];
        $invoice_data = $this->mdl_invoices->find_by_id($invoice_id);

        if(!$invoice_data)
            return false;
        if($update_data['pre_invoice_status'] == $update_data['new_invoice_status'])
            return true;

		$oldStatus = $this->mdl_invoice_status->get_by(['invoice_status_id' => $update_data['pre_invoice_status']]);
		$newStatus = $this->mdl_invoice_status->get_by(['invoice_status_id' => $update_data['new_invoice_status']]);

		if($newStatus->completed && $this->mdl_estimates->get_total_estimate_balance($invoice_data->estimate_id) > 0)
		    return false;

        $this->mdl_estimates->status_log([
            'status_type' => 'invoice',
            'status_item_id' => $invoice_id,
            'status_value' => $update_data['new_invoice_status'],
            'status_date' => time()
        ]);
        $this->mdl_invoices->update_invoice([
            'in_status' => $update_data['new_invoice_status'],
            'payment_mode' => isset($update_data['payment_mode']) ? $update_data['payment_mode'] : "",
            'link_hash' => '',
            'overpaid' => isset($update_data['overpaid'])? $update_data['overpaid'] : null
        ], ['id' => $invoice_id]);

		//Update status code

		if ((int)$newStatus->completed) {
			//update discount to 0
			$this->mdl_invoices->update_invoice(['interest_rate' => 0], ['id' => $invoice_id]);
			$this->mdl_invoices->update_invoice_interst(['nill_rate' => '1'], ['invoice_id' => $invoice_id]);
		} else {
            //create a new job for synchronization in QB
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice_id, 'qbId' => $invoice_data->invoice_qb_id]));
        }

        $data['overdue_date'] = date('Y-m-d', strtotime('+' . \application\modules\invoices\models\Invoice::getInvoiceTerm($invoice_data->client_type ?? null) . ' days'));

		if((int)$newStatus->is_overdue) {
			//insert interest into invoice_interest
			$this->mdl_invoices->update_interest($data, $invoice_id);
			$this->mdl_invoices->insert_interest(['invoice_id'=>$invoice_id, 'overdue_date'=>$data['overdue_date'], 'rate'=>INVOICE_INTEREST]);
			$this->mdl_invoices->update_all_invoice_interes($invoice_data->estimate_id);
		}

		if((int)$newStatus->is_hold_backs)
			$this->mdl_invoices->update_interest($data, $invoice_id);


		$update_msg = "Status for invoice " . $invoice_data->invoice_no . ' was modified from ' . $oldStatus->invoice_status_name . ' to ' . $newStatus->invoice_status_name;

		$this->load->model('mdl_followups');
		$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $invoice_id, 'fu_status' => 'postponed']);
		$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $invoice_id, 'fu_status' => 'new']);

		if($fuRowNew && !empty($fuRowNew))
			$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice status was changed']);
		elseif($fuRowPost && !empty($fuRowPost))
			$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice status was changed']);

		make_notes($invoice_data->client_id, $update_msg, 'system', intval($invoice_data->invoice_no));

        return true;
	} // End ajax_change_invoice_status

	function save_due_date() {
		if($this->input->post('id') != null && $this->input->post('id') != ''){
            $id = (int)$this->input->post('id');
            $this->load->model('mdl_invoices');
            $invoice = $this->mdl_invoices->find_by_id($id);
            if(!$invoice) {
                return $this->response([
                    'status' => false,
                    'message' => 'Error Updating Dates'
                ], 200);
            }
            $data = [];

			$created_date = DateTime::createFromFormat(getDateFormat(), $this->input->post('date_created'));
            $created_date = $created_date ? $created_date->format('Y-m-d') : $created_date;
            if($created_date) {
                $data['date_created'] = $created_date;
            } else {
                $created_date = $invoice->date_created;
            }

			$overdue_date = DateTime::createFromFormat(getDateFormat(), $this->input->post('overdue_date'));
            $overdue_date = $overdue_date->format('Y-m-d');
            if($overdue_date) {
                $data['overdue_date'] = $overdue_date;
            }

			if(strtotime($overdue_date) >= strtotime($created_date)){
				if($this->mdl_invoices->update_invoice($data, ['id' => $this->input->post('id')])){
                    pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
					return $this->response([
						'status' => true,
						'message' => 'Dates Successfully Updated'
					], 200);
				} else {
					return $this->response([
						'status' => FALSE,
						'message' => 'Error Updating Due Date'
					], 200);
				}
			} else {
				return $this->response([
					'status' => FALSE,
					'message' => 'Incorrect Dates'
				], 200);
			}
		} else {
			return $this->response([
				'status' => FALSE,
				'message' => 'No Invoice Id Provided'
			], 200);
		}
	}

//*******************************************************************************************************************
//*************
//*************
//*************										Send Invoice
//*************
//*************
//*******************************************************************************************************************

	/* NOT USED/ now:08.04.2020 	delete: 08.05.2020 if not error notifications

	function send_invoice()
	{
		$invoice_id = $this->input->post("invoice_id");
		if (!empty($invoice_id)) {
			$invoice_data = $this->mdl_invoices->find_by_id($invoice_id);
			$wdata = array('id' => $invoice_id);
			$mailsent = $this->_send_secure_link($invoice_data, $wdata, $invoice_id);

			$is_sent_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_sent' => 1]), 0);
			$update_data = ['in_status' => 'Sent'];

			$updated = $this->mdl_invoices->update_invoice($update_data, $wdata);
			if ($mailsent == 1) {
				echo 'success-mail';
			} elseif ($mailsent == 2) {
				echo 'email-not-exists';
			} else {
				echo 'success-mail-fail';
			}
		} else {
			echo "Error: error in sending sucre link. please try again";
		}
	}
	*/


	//************************************************************************************************
	//***********
	private function _send_secure_link($invoice_data, $wdata, $invoice_id)
	{
		// utilities helper
		$this->load->helper('utilities');
		// secure link
		$hashkey = uniqid();
		$secure_link = base_url() . "payment/online/" . $hashkey;

		$link_hash_valid_till = date("Y-m-d H:i:s", strtotime("+" . _HASH_KEY_VALID_DAYS . " days"));
		// updating db table
		$updated = $this->mdl_invoices->update_invoice(array('link_hash' => $hashkey, 'link_hash_valid_till' => $link_hash_valid_till), $wdata);

		$client_id = $invoice_data->client_id;
		// getting client details
		$clients_data = $this->mdl_clients->get_clients('', array('client_id' => $invoice_data->client_id));
		$client_data = $clients_data->result_array();
		if (!empty($client_data[0])) {
			$client_data = $client_data[0];
		}

		//Set title:
		$data['title'] = $this->_title . ' - Leads';

		//Get invoices data
		$data['invoice_data'] = $this->mdl_invoices->find_by_id($invoice_id);

		//Get workorder informations - using common function from MY_Models;
		$workorder_id = $data['invoice_data']->workorder_id;
		$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

		//Get estimate informations - using common function from MY_Models;
		$estimate_id = $data['workorder_data']->estimate_id;
		$data['estimate_data'] = $this->mdl_estimates->find_by_id($estimate_id);

		$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($invoice_id);
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));

		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

		//Get client_id and retrive client's information:
		$id = $data['estimate_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($client_id);

		$pdf = $this->invoice_pdf_generate($invoice_id);

		$html = $pdf['html'];
		$file = $pdf['file']; //INVOICES_UPLOAD_PATH."invoice_".$estimate_id.".pdf";

		$subject = _SECURE_LINK_TO_CLIENT;

		$body = "Hi, " . $client_data["client_name"] . "<br><br>";

		$body .= "Please find enclosed invoice for " . $client_data["client_address"] . ", " . $client_data["client_city"] . ".<br><br>";

		//$body .= "You may click the <a href='".$secure_link."'>Payment Link</a> to pay though our online credit card processing system, or give us a call and we can put the charge through for you.<br>";
		//$body .= "Alternatively, you can mail us a cheque. We appreciate your timely payment.<br><br>";

		$body .= " Regards,<br><br>";

		$body .= "<B>" . $this->config->item('default_email_from_second') . "</B><br><br><br>";

		$to = $data['client_contact']['cc_email'];
		if (empty($to)) {
			return 2;
		}
		// sending secure link in mail
		if (send_mail($to, $subject, $body, _ADMIN_EMAIL, _ADMIN_NAME, $file)) {
			$mailsent = 1;
		}
		unlink($file);
		return $mailsent;
	}

//*******************************************************************************************************************
//*************
//*************										Invoice PDF(); 
//*************
//*************											*** Generating pdf of the invoice using estimate_id; ***
//*************
//*******************************************************************************************************************	

	function invoice_pdf($invoice_id = NULL, $lead_id = NULL)
	{
		
		if (!isset($invoice_id) && !$lead_id) { // NB: Set to redirect to index if variable is null or not set;
			redirect('invoices/', 'refresh');
		} else {
			$data = $this->invoice_pdf_generate($invoice_id, $lead_id);
			$this->load->library('mpdf');
            $css = file_get_contents('assets/css/global_invoice_pdf.css');
            $this->mpdf->WriteHTML($css,\Mpdf\HTMLParserMode::HEADER_CSS);
			$this->mpdf->WriteHTML($data['html']);
            foreach ($data['pdf_files'] as $file) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
            }
			$this->mpdf->Output($data['file'], 'I');
		}
		// end else;
	}

	//End invoice_pdf();

	function invoice_pdf_generate($invoice_id = NULL, $lead_id = NULL)
	{
        $this->load->model('mdl_estimates_bundles');
		//Set title:
		$data['title'] = $this->_title . ' - Invoices';

		//Get invoices data
        if($invoice_id) {
            $where['invoices.id'] = $invoice_id;
        }

        if($lead_id) {
            $where['estimates.lead_id'] = $lead_id;
        }

        $data['invoice_data'] = $this->mdl_invoices->find_by_field($where);

		if(!$data['invoice_data']) {
            page_404(['message'=>'This invoice does not exist']);
        }

		$invoice_no = $data['invoice_data']->invoice_no;
		$invoice_id = $data['invoice_data']->id;
		//Get workorder informations - using common function from MY_Models;
		$workorder_id = $data['invoice_data']->workorder_id;
		$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

		//Get estimate informations - using common function from MY_Models;
		$estimate_id = $data['workorder_data']->estimate_id;

        $estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id));
        $data['estimate_data'] = $this->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
		
		//estimate services
        $estimateServicesData = $this->mdl_estimates->find_estimate_services($estimate_id, ['estimates_services.service_status <>' => 1]);
        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];
        foreach ($estimateServicesData as $key => $value){
            if($value['is_bundle']){
                $bundleRecords = $this->mdl_estimates_bundles->get_many_by(['eb_bundle_id' => $value['id']]);
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
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
		$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($invoice_id); 
	 
		//echo $this->db->last_query();exit;
		//Get client_id and retrive client's information:
		$id = $data['estimate_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($id);

		$estClPath = 'uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/tmp/';
		$pdfFiles = $data['invoice_data']->invoice_pdf_files ? json_decode($data['invoice_data']->invoice_pdf_files) : [];
        $pdfs = [];
		$pictures['files'] = $pdfFiles;
		if(!$pictures['files'])
			$pictures['files'] = array();
		foreach($pictures['files'] as $key=>$file)
		{
			if(pathinfo($file)['extension'] != 'pdf')
				$data['estFiles'][] = $file;
			elseif(pathinfo($file)['extension'] == 'pdf')
				$pdfs[] = $file;
		}
		$file = "Invoice " . $invoice_no . " - " . str_replace('/', '_', $data['estimate_data']->lead_address) . '.pdf';


        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/invoice_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'invoice_pdf', $data, TRUE);
        } else {
            $html = $this->load->view('includes/pdf_templates/invoice_pdf', $data, TRUE);
        }

        $brand_id = get_brand_id($data['estimate_data'], $data['client_data']);
        $html = ClientLetter::parseCustomTemplates($estimate_id, $html, $brand_id);

		return array('file' => $file, 'html' => $html, 'pdf_files' => $pdfs);
	}


	//*******************************************************************************************************************
	//*************
	//*************										function to upload payment mode file
	//*************
	//*******************************************************************************************************************	

	public function payment_file_upload()
	{
		//upload function
		if ($_FILES['payment_file']['size'] > 0) {
			$this->load->helper("utilities");
			$invoice_id = $_POST["invoice_id"];

			// getting invoice details
			$invoice_data = $this->mdl_invoices->find_by_id($invoice_id);

			$invoice_no = $invoice_data->invoice_no; // invoice no

			// getting payment files details
			$payment_files = $this->payment_files->get_payment_file(array("invoice_id" => $invoice_id));
			$file_no = "";
			$last_payment_file_index = 0;
			if (isset($payment_files[0]["payment_file"])) {
				$old_filename = $payment_files[0]["payment_file"];
				$old_filename = substr($old_filename, 0, -4);
				$old_filename_parts = explode("_", $old_filename);

				if (count($old_filename_parts) == 3) {
					$last_payment_file_index = $old_filename_parts[2];
				} elseif (count($old_filename_parts) == 2) {
					$last_payment_file_index = 1;
				}
			}

			if ($last_payment_file_index > 0) {
				$file_no = "_" . ($last_payment_file_index + 1);
			}

			if ($_FILES['payment_file']['size'] > 6000000) {
				echo "ERROR: filesize";
			}
			/* upload code */
			$config['allowed_types'] = 'gif|jpg|jpeg|png|doc|pdf|txt';
			$config['overwrite'] = FALSE;
			$config['max_size'] = '7000';

			$picture_file = $_FILES['payment_file']['name'];

			$exts = preg_split("[\.]", strtolower($picture_file));

			do {
				$rand = rand(10000000, 99999999);
				// payment filename
				$uploadFilename = 'Payment_' . $invoice_no . $file_no . '.' . $exts[count($exts) - 1];
			} while (get_file_info(base_url() . PAYMENT_FILES_PATH . $uploadFilename, 'name'));

			$config['file_name'] = $uploadFilename;
			$config['upload_path'] = PAYMENT_FILES_PATH;
			$this->load->library('upload', $config);

			if (!$this->upload->do_upload('payment_file')) {
				echo "ERROR: " . $this->upload->display_errors();
			} else {
				$upload_data = $this->upload->data();
				echo "SUCCESS: " . $upload_data["file_name"];
			}
			//resizeImage(PAYMENT_FILES_PATH . $uploadFilename);
		}
	}

	//*******************************************************************************************************************
	//*************
	//*************										function to delete payment mode file
	//*************
	//*******************************************************************************************************************	

	public function del_payment_file()
	{
		if (isAdmin()) {
			$id = $this->input->post("id");
			if (!empty($id)) {
				$payment_details = $this->payment_files->get_payment_file_by_id($id);

				if (!empty($payment_details[0]["payment_file"])) {
					@unlink(PAYMENT_UPLOAD_PATH . $payment_details[0]["payment_file"]);
				}
				$res = $this->payment_files->delete($id);
				if ($res == 1) {
					echo "_DELETED";
				} else {
					echo "_NOT_DELETED";
				}
			} else {
				echo "_NO_ID";
			}
		} else {
			echo "_NOT_ADMIN";
		}
	}

	//*******************************************************************************************************************
//*************
//*************											interset									Create leads function;
//*************
//*******************************************************************************************************************


	public function interset($id = '')
	{
		$res = TRUE;
		$res1 = FALSE;
		
		if ($id) {
			$invoice = $this->mdl_invoices->getEstimatedData($id);
			$dbInterest = isset($invoice->interest_status) && $invoice->interest_status == 'Yes' ? 'Yes' : false;
			if($this->input->post('check_overdue') != $dbInterest) {
				$data['interest_status'] = $this->input->post('check_overdue') ? strip_tags($this->input->post('check_overdue')) : 'No';
				if ($data['interest_status'] == 'Yes')
					make_notes($invoice->client_id, 'Disabled interest for "' . $invoice->invoice_no . '"', 'system', intval($invoice->invoice_no));
				if ($data['interest_status'] == 'No')
					make_notes($invoice->client_id, 'Enabled interest for "' . $invoice->invoice_no . '"', 'system', intval($invoice->invoice_no));
				$res = $this->mdl_invoices->update_interest($data, $invoice->id);
				$res = TRUE;
			}
			
			if ($this->input->post('interest_rate') !== FALSE) {
				$discount['discount_amount'] = abs(strip_tags($this->input->post('interest_rate')));
				$discount['discount_percents'] = $this->input->post('discount_percents');
				$discount['discount_comment'] = $this->input->post('discount_comment');
				$discount_data = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $id));
				$note_comment = '';
				if(isset($discount['discount_comment']) && trim($discount['discount_comment']) != ''){
					$note_comment = '<br>Comment:<br>' . nl2br($discount['discount_comment']);
				}
				$discountText = money($discount['discount_amount']) . $note_comment;
				if($discount['discount_percents'])
					$discountText = $discount['discount_amount'] . '%' . $note_comment;
				if ($discount_data && !empty($discount_data)) {
					$this->mdl_clients->update_discount($discount_data['discount_id'], $discount);
					make_notes($discount_data['client_id'], 'Updated discount "' . $discount_data['estimate_no'] . '" - ' . $discountText, 'system', $discount_data['lead_id']);
				} else {
					$discount['discount_date'] = time();
					$discount['estimate_id'] = $id;
					$this->mdl_clients->insert_discount($discount);
					$discount_data = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $id));
					make_notes($discount_data['client_id'], 'Set discount "' . $discount_data['estimate_no'] . '" - ' . $discountText, 'system', $discount_data['lead_id']);
				}
				$res1 = TRUE;
			}
			$this->mdl_estimates->update_estimate_balance($id);
			$this->mdl_invoices->update_all_invoice_interes($id);

			if ($res) {
			    if(!empty($invoice)) {
                    //create a new job for synchronization in QB
                    $invoice = $this->mdl_invoices->find_by_id($invoice->id);
                    pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
                    $link = base_url($invoice->invoice_no);
                }
				if ($res1)
					$mess = message('success', 'Discount updated successfuly!');
				elseif ($res)
					$mess = message('success', 'Interest updated successfuly!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$link = base_url('invoices');
				$mess = message('alert', 'Something went wrong. Please Try again!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($_SERVER['HTTP_REFERER']);
			}

		} else {
			$link = base_url('invoices');
			$mess = message('alert', 'Something went wrong. Please Try again!');
			$this->session->set_flashdata('user_message', $mess);
			redirect($_SERVER['HTTP_REFERER']);
		}
	}


    function send_pdf_to_email()
    {
        $invoice_id = $this->input->post('id');
        if (!intval($invoice_id))
            die(json_encode(array('type' => 'error', 'message' => 'Estimate id is not valid')));

        $note['to'] = $to = $this->input->post('email_tags');
        $cc = $bcc = '';
		if($this->input->post('cc') != null && $this->input->post('cc') != ''){
			$note['cc'] = $cc = $this->input->post('cc');
		} /*elseif(config_item('default_cc')) {
			$note['cc'] = $cc = $this->config->item('default_cc');
		}*/
        if($this->input->post('bcc') != null && $this->input->post('bcc') != ''){
            $note['bcc'] = $bcc = $this->input->post('bcc');
        } elseif(config_item('default_invoice_bcc')) {
			$note['bcc'] = $bcc = $this->config->item('default_invoice_bcc');
		}
        $note['subject'] = $subject = $this->input->post('subject');
        $text = $this->input->post('text');
        $note['from'] = $from_email = $this->input->post('email_from');//$this->config->item('account_email_address');
        $data['invoice_data'] = $this->mdl_invoices->find_by_id($invoice_id);
        $data['estimate_data'] = $this->mdl_estimates->find_by_id($data['invoice_data']->estimate_id);
        if (empty($data['invoice_data']))
            die(json_encode(array('type' => 'error', 'message' => 'Estimate id is not defined')));

        $check = check_receive_email($data['invoice_data']->client_id, $to);

        if($check['status'] != 'ok')
            die(json_encode(array('type' => $check['status'], 'message' => $check['message'])));

        if($data['estimate_data']->user_signature)
            $text .= $data['estimate_data']->user_signature;

        $data['client_data'] = $this->mdl_clients->find_by_id($data['invoice_data']->client_id);

        $pdf = $this->invoice_pdf_generate($invoice_id);

        $this->load->library('mpdf');
        $this->mpdf->WriteHTML($pdf['html']);
        foreach ($pdf['pdf_files'] as $file) {
            $this->mpdf->AddPage();
            $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
        }
        $attach = $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf['file'];

        if(file_exists($file)) {
            $attach = $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('.pdf', '', $pdf['file']) . '-' . uniqid() . '.pdf';
        }

        $this->mpdf->Output($file, 'F');

        $this->load->library('email');
        $config['mailtype'] = 'html';
        $toDomain = substr(strrchr($to, "@"), 1);
        if(array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
            $config = $this->config->item('smtp_mail');
            $note['from'] = $from_email = $config['smtp_user'];
        }

        $this->email->initialize($config);

        //checking if a file in not larger than default_pdf_size from the settings
        if(filesize($file) < config_item('default_pdf_size')
            && strlen(base64_encode(file_get_contents($file))) < config_item('default_pdf_size')){
            $this->email->attach($file);
        }else{
            $invoice_link = '<div style="text-align: center">';
            $href = base_url("payments/invoice/" . md5($data["invoice_data"]->invoice_no . $data["invoice_data"]->client_id));
            $invoice_link .= '<a href="' . $href . '" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color: #81BA53;border-top: 10px solid #81BA53;border-right: 18px solid #81BA53;border-bottom: 10px solid #81BA53;border-left: 18px solid #81BA53;font-size: 20px;" target="_blank" data-saferedirecturl="' . $href . '">View Invoice</a>';
            $invoice_link .= '</div>';
            $text .= $invoice_link;
        }

        if($this->input->post('like'))
            $text .= $this->load->view('new_invoice_letter_likes', $data, TRUE);

        $text .= '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->config->item('unsubscribe_link') . md5($data['invoice_data']->client_id) . '">unsubscribe</a> at any time.</div>';

        $this->email->to($to);
        if(isset($cc) && $cc != '')
            $this->email->cc($cc);
        if(isset($bcc) && $bcc != '')
            $this->email->bcc($bcc);
        $this->email->from($from_email, $this->config->item('company_name_short'));
        $this->email->subject($subject);

        $this->email->message($text);
        $status['type'] = 'success';
        $status['message'] = 'Email sent. Thanks';

        $send = $this->email->send();

        if (!is_array($send) || isset($send['error'])) {
            $status['type'] = 'error';
            $status['message'] = 'Oops! Email send error. Please try again';

            if (isset($send['error'])) {
                $status['message'] = $send['error'];
            }

            die(json_encode($status));
        }

        $entities = [
            ['entity' => 'invoice', 'id' => $data['invoice_data']->id],
            ['entity' => 'client', 'id' => $data['invoice_data']->client_id]
        ];
        $this->email->setEmailEntities($entities);

        $default_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'default' => 1]), 0);
        $sent_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_sent' => 1]), 0);

        if ($data['invoice_data']->in_status == (int)$default_status) {
            $updated = $this->mdl_invoices->update_invoice(['in_status' => (int)$sent_status], ['id' => $data['invoice_data']->id]);
            $insert = array('status_type' => 'invoice', 'status_item_id' => $data['invoice_data']->id, 'status_value' => $sent_status, 'status_date' => time());
            $this->mdl_estimates->status_log($insert);
        }
        $note_id = make_notes(
            $data['invoice_data']->client_id,
            'Invoice ' . $data['invoice_data']->invoice_no . ' sent to "' . $to . '".',
            'email',
            intval($data['invoice_data']->invoice_no),
            $this->email
        );

        $dir = 'uploads/notes_files/' . $data['invoice_data']->client_id .'/' . $note_id . '/';

        $pattern = "/<body>(.*?)<\/body>/is";
        preg_match($pattern, $text, $res);
        $note['text'] = isset($res[1]) ? $res[1] : $text;

        $this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['invoice_data']->invoice_no . '.pdf', 'F');
        @unlink($file);
        bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['invoice_data']->invoice_no . '.pdf', $dir . $data['invoice_data']->invoice_no . '.pdf', ['ContentType' => 'application/pdf']);
        @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['invoice_data']->invoice_no . '.pdf');
        bucket_write_file($dir . 'Content.html', $this->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);

        //create a new job for synchronization in QB
        pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice_id, 'qbId' => $data['invoice_data']->invoice_qb_id]));

        die(json_encode($status));
    }
	
	function ajax_qa_select()
	{
		$this->load->model('mdl_qa');
		
		$data['qa'] = $this->mdl_qa->find_all_with_limit(array(), '', '', 'qa_type_int', array('qa_status' => 1, 'qa_type_int' => $_POST['type']));
		$data['status'] = 'ok';
		die(json_encode($data));
	}

	function ajax_pdf_file()
	{
		$dir = $this->input->post('name');
		$estimate_id = $this->input->post('estimate_id');
		$invoice = $this->mdl_invoices->find_by_fields(['estimate_id' => $estimate_id]);
		$files = $invoice->invoice_pdf_files ? json_decode($invoice->invoice_pdf_files) : [];
		$key = array_search($dir, $files);
		if($key !== FALSE)
			unset($files[$key]);
		else
			$files[] = $dir;
		$files = array_values($files);
		$str = json_encode($files);
		$this->mdl_invoices->update($invoice->id, array('invoice_pdf_files' => $str));
	}

	function delete($id)
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		else{
            //create a new job for synchronization in QB
            $invoice = $this->mdl_invoices->find_by_id($id);
            $wo_id = $invoice->workorder_id;
			$client_id = $invoice->client_id;
            
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $id, 'qbId' => $invoice->invoice_qb_id]));

            if($this->mdl_invoices->delete_invoice_new($id))
			{
				$this->load->model('mdl_followups');
				$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $id, 'fu_status' => 'postponed']);
				$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $id, 'fu_status' => 'new']);
				//echo '<pre>'; var_dump($fuRowNew); die;
				if($fuRowNew && !empty($fuRowNew))
					$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice was deleted']);
				elseif($fuRowPost && !empty($fuRowPost))
					$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - invoice was deleted']);

				$deleteInvoiceStatusId = $this->mdl_workorders->getDeleteInvoiceStatusId();
				$this->mdl_workorders->update_workorder(array('wo_status' => $deleteInvoiceStatusId), array('id' => $wo_id));
				$link = base_url($client_id);
				$mess = message('success', 'Invoice was successfully deleted!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($link);
			}
		}
	}
	
	function invoices_overdue()
	{
		$this->load->model('mdl_invoices');
		
		$startDate = $data['start'] = strtotime('2014-01-01');
		$endDate = $data['end'] =  strtotime(date('Y-m-d'));
		$data['type'] = date('Y', $startDate);
		$data['title'] = 'Overdue Statistics';
		$data['report'] = [];
		$data['from'] = '01-01';
		$data['to'] = '01-31';

		$currDate = $startDate;
		$data['report']['yearly'][date('Y', $currDate)] = 0;
		while($currDate < $endDate) {
			$invoices = [];
			if(date('m', $currDate) == 01)
			{
				$data['report']['yearly'][date('Y', $currDate)] = 0;
				$invoices = $this->mdl_invoices->invoice_overdue_sum(['status_date >=' => $currDate, 'status_date <=' => strtotime(date('Y-m-t', $currDate))]);
				
				if(!empty($invoices))
				{

					foreach($invoices as $k=>$v)
					{
						$invoice_array = $this->mdl_invoices->invoices(['invoices.id' => $v->invoice_id]);
						$invoice = (!empty($invoice_array))?$invoice_array[0]:[];
						$data['report']['invoices'][date('Y', $currDate)][] = $invoice;
					}
				}
			}

			$invoices = $this->mdl_invoices->invoice_overdue_sum(['status_date >=' => $currDate, 'status_date <=' => strtotime(date('Y-m-t', $currDate))], true);

			if(isset($invoices->sum) && $invoices->sum)
			{
				$data['report'][date('Y', $currDate)][date('m', $currDate)] = round($invoices->sum, 2);
				$data['report']['yearly'][date('Y', $currDate)] += round($invoices->sum, 2);
			}
			else
				$data['report'][date('Y', $currDate)][date('m', $currDate)] = 0;

			$currDate = strtotime(date('Y-m-t', $currDate)) + 86400;
		}
		
		$this->load->view('overdue_report', $data);
	}
	
	function ajax_get_invoices()
	{
		$this->load->model('mdl_invoices');
		$years = json_decode($this->input->post('all_years'));
		$from = $this->input->post('from');
		$to = $this->input->post('to');
		
		$result = [];
		foreach($years as $k=>$v)
		{
			$data['invoices'] = [];
			$invoices = $this->mdl_invoices->invoice_overdue_sum(['status_date >=' => strtotime($k . '-' . $from), 'status_date <=' => strtotime($k . '-' . $to)]);
			if(!empty($invoices))
			{
				
				foreach($invoices as $key=>$val)
				{
					$invoice_array = $this->mdl_invoices->invoices(['invoices.id' => $val->invoice_id]);
					$data['invoices'][] = (!empty($invoice_array))?$invoice_array[0]:[];
					$result['data-'.$k]['html'] = $this->load->view('tab_invoice_overvue', $data, TRUE);
				}
			}
			
		}
		die(json_encode($result));
	}
	function ajax_edit_notes()
	{
		$wdata['id'] = $this->input->post('id');
		$data['invoice_notes'] = strip_tags($this->input->post('text'));
		if(!$wdata || empty($data))
			die(json_encode(FALSE));
		$invoice_updated = $this->mdl_invoices->update_invoice($data, $wdata);
		echo(TRUE);
	}

	/***********************Invoice Statuses******************/
	public function invoice_status()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_invoice_status');
		$data['title'] = "Invoice Status";

		//get employees
		$data['statuses'] = $this->mdl_invoice_status->order_by('priority')->get_many_by(['invoice_status_active' => 1]);
		
		$this->load->view('index_invoice_status', $data);
	}

	function ajax_save_invoice_status()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_invoice_status');
		$id = (int)$this->input->post('status_id');
		$data['invoice_status_name'] = strip_tags($this->input->post('status_name', TRUE));
		
		if ($id) {
			$this->mdl_invoice_status->update($id, $data);
			die(json_encode(array('status' => 'ok')));
		}
		$id = $this->mdl_invoice_status->insert($data);
		
		return ajax_response('ok');
	}

	function ajax_delete_invoice_status()
	{
		if ($this->session->userdata('user_type') != "admin")
			show_404();
		
		$this->load->model('mdl_invoice_status');
		$id = $this->input->post('status_id');
		$status = $this->input->post('status');
		if ($id != '')
			$this->mdl_invoice_status->update($id, array('invoice_status_active' => $status));
		die(json_encode(array('status' => 'ok')));
	}
	/***********************END Invoice Statuses******************/

	function ajax_get_payment_methods()
	{
		$methods = $this->config->item('payment_methods');
		$default = $this->config->item('default_cc');
		die(json_encode(['status' => TRUE, 'methods' => $methods, 'default' => $default]));
	}

    function ajax_get_invoice_statuses()
    {
        $this->load->model('mdl_invoice_status');
        return $this->response($this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1])->order_by('default', 'DESC'),200);
    }

    public function ajax_statuses_update()
    {
        $data   = $this->input->post('statuses') ?? [];
        $column = 'priority';

        foreach ($data as $key => $id) {
            $priority[$column]  = $key;

            $this->mdl_invoice_status->update($id, $priority, false);
        }

        $this->response(['result' => true]);
    }

}
//end of file invoices.php
