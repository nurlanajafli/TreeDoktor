<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Confirm extends MX_Controller
{
	var $workorder;
    var $client;
    var $estimate;
	function __construct()
	{
	    parent::__construct();
		$hash = $this->uri->segment(2);

		if(!$hash)
			redirect(base_url());
		else
		{
			$this->load->model('mdl_workorders');
            $this->load->model('mdl_estimates_orm');
            $this->load->model('mdl_clients');
			$statusConfirmByClient = $this->mdl_workorders->getConfirmByClientId();
			if($statusConfirmByClient===FALSE)
				$statusConfirmByClient = $this->mdl_workorders->getDefaultStatusId();

			$this->workorder = $this->mdl_workorders->find_by_fields(array('MD5(CONCAT(workorder_no, id, wo_status)) = ' => $hash, 'wo_status <>' => (int)$statusConfirmByClient));


			if(!$this->workorder)
				redirect(base_url());

            $this->estimate = $this->mdl_estimates_orm->get($this->workorder->estimate_id);
            $this->client = $this->mdl_clients->find_by_id($this->workorder->client_id);
		}
	}

	function index()
	{
		$statusConfirmByClient = $this->mdl_workorders->getConfirmByClientId();
		if($statusConfirmByClient===FALSE)
			$statusConfirmByClient = $this->mdl_workorders->getDefaultStatusId();


		$brand_id = get_brand_id($this->estimate, $this->client);
		$this->mdl_workorders->update($this->workorder->id, array('wo_status' => (int)$statusConfirmByClient));
		$this->load->view('thankyou', ['brand_id'=>$brand_id]);
	}

}
