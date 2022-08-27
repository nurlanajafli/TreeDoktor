<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Qa extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Qa Controller;
//*************
//*******************************************************************************************************************	

	function __construct()
	{

		parent::__construct();


		if (!isUserLoggedIn()) {
			redirect('login');
		}
		if (is_cl_permission_none())
			redirect('dashboard');

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_qa', 'mdl_qa');
		
	}

//*******************************************************************************************************************
//*************
//*************																							Index Function;
//*************
//*******************************************************************************************************************	

	public function index()
	{
        show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_qa');
		$data['title'] = 'Quality Assurance';
		$data['qa'] = $this->mdl_qa->find_all_with_limit([], '', '', 'qa_status DESC, qa_type_int');

//		dd($data);
		$this->load->view('index_qa', $data);
	}
	
	function ajax_save_qa()
	{
        show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_qa');
		$qa_id = $this->input->post('qa_id');
		$data['qa_name'] = strip_tags($this->input->post('qa_name', TRUE));
		$data['qa_description'] = strip_tags($this->input->post('qa_description', TRUE));
		$data['qa_type_int'] = intval($this->input->post('qa_type_int'));
		$data['qa_rate'] = intval($this->input->post('qa_rate', TRUE));
		$data['qa_status'] = 1;
		if ($qa_id) {
			$this->mdl_qa->update($qa_id, $data);
			die(json_encode(array('status' => 'ok')));
		}
		$this->mdl_qa->insert($data);
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_delete_qa()
	{
        show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_qa');
		$qa_id = $this->input->post('qa_id');
		$status = $this->input->post('status') ? 1 : 0;
		if ($qa_id)
			$this->mdl_qa->update($qa_id, ['qa_status' => $status]);
		die(json_encode(array('status' => 'ok')));
	}
	
	function invoices($status = NULL)
	{
        show_404();
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_invoice_status');

		$data['status'] = $status;
		if(isset($status) && ($data['status'] != 'likes' || $data['status'] != 'dislikes' || $data['status'] != 'all' || $data['status'] != 'no_response'))
			redirect('qa/invoices');
		$data['title'] = 'Quality Assurance | Estimates';
		$where['status_type'] = 'invoice';
		
		$data['from'] = $where['status_date >='] = strtotime(date('Y-m-01'));
		$data['to'] = $where['status_date <='] = strtotime(date('Y-m-t'));
		
		if ($this->input->post('from'))
			$data['from'] = $where['status_date >='] = strtotime($this->input->post('from'));
		if ($this->input->post('to'))
			$data['to'] = $where['status_date <='] = strtotime($this->input->post('to'));
		
		$paid_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'completed'=>1]), 0);
		
		$data['invoices_all'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		$where['invoice_like'] = 1;
		$data['invoices_like'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		$where['invoice_like'] = 0;
		$data['invoices_dislike'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		unset($where['invoice_like']);
		$where['invoice_like IS NULL'] = NULL;
		$data['invoices_response'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		unset($where['invoice_like IS NULL']);
		
		
		if($this->input->post('status') == 'likes')
			$where['invoice_like'] = 1;
		elseif($this->input->post('status') == 'dislikes')
			$where['invoice_like'] = 0;
		elseif($this->input->post('status') == 'no_response')
			$where['invoice_like IS NULL'] = NULL;
		
		$data['estimates'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id');
		
		$this->load->view('qa_estimates', $data);
	}
	function ajax_invoices()
	{
        show_404();
		$this->load->model('mdl_invoices');
		$where['status_type'] = 'invoice';
		if ($this->input->post('from'))
			$where['status_date >='] = strtotime($this->input->post('from'));
		if ($this->input->post('to'))
			$where['status_date <='] = strtotime($this->input->post('to'));

		$result['invoices_all'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		$where['invoice_like'] = 1;
		$result['invoices_like'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		$where['invoice_like'] = 0;
		$result['invoices_dislike'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		$where['invoice_like IS NULL'] = NULL;
		$result['invoices_response'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id', true);
		unset($where['invoice_like']);
		unset($where['invoice_like IS NULL']);
		if($this->input->post('status') == 'likes')
			$where['invoice_like'] = 1;
		elseif($this->input->post('status') == 'dislikes')
			$where['invoice_like'] = 0;
		elseif($this->input->post('status') == 'no_response')
			$where['invoice_like IS NULL'] = NULL;
			
		$data['estimates'] = $this->mdl_qa->get_status_full_data($where, [$paid_status], 'invoices', 'id');
		
		$result['table'] = $this->load->view('estimates_table', $data, TRUE);
		die(json_encode($result));
	}
	
	
}
//end of file qa.php
