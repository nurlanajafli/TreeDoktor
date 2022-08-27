<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Unsubscribe extends MX_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	function index($module = 'estimate', $hash = NULL)
	{
		if($module == 'estimate')
		{
			$this->load->model('mdl_estimates');
			$estimate = $this->mdl_estimates->find_by_fields(['MD5(CONCAT(estimate_id, client_id)) =' => $hash]);
			if($estimate && !$estimate->unsubscribe)
			{
				$this->mdl_estimates->update($estimate->estimate_id, ['unsubscribe' => 1]); 
				$this->load->view('index');
			}
			else
				redirect($this->config->item('company_site'));
		}
		elseif($module == 'unsubscribeAll')
		{
			$this->load->model('mdl_clients');
			$client = $this->mdl_clients->find_by_fields(['MD5(client_id) =' => $hash]);
			if($client && !$client->client_unsubscribe)
			{
				$this->mdl_clients->update($client->client_id, ['client_unsubscribe' => 1]); 
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				
				
				
				$this->email->to($this->config->item('account_email_address'));
				$this->email->from($this->config->item('account_email_address'));
				$this->email->subject('Unsubscribe');				
				$this->email->message('Client <a href="'.base_url('client/' . $client->client_id).'">'. $client->client_name .'</a> was unsubscribed!');
				$this->email->send();
				 
				$this->load->view('index');
			}
			else
				redirect($this->config->item('company_site'));
		}
	}
}
