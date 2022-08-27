<?php
class Mdl_followups extends JR_Model
{
	protected $_table = 'followups';
	protected $primary_key = 'fu_id';
	
	public function __construct() {
		parent::__construct();
	}

    /**
     * @param int $fu_client_id
     * @return mixed
     * @throws ErrorException
     */
    public function getClient(int $fu_client_id) {
        if (empty($fu_client_id)) {
            throw new ErrorException('No client id found');
        }
        $this->db->where('client_id', $fu_client_id);
        $this->db->select('clients.*, clients_contacts.*');
        $this->db->join('followup_settings', 'fu_fs_id = fs_id');
        $this->db->join('clients', 'followups.fu_client_id = clients.client_id', 'left');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');

        return $this->db->get($this->_table)->row();
    }

    function get_list($status = NULL, $where = FALSE, $limit = FALSE, $offset = FALSE) {
		if($status)
			$this->db->where('fu_status', $status);
		if($where)
			$this->db->where($where);
		$this->db->select('followups.*, followup_settings.*, clients.*, clients_contacts.*, users.*, IFNULL(estimates.estimate_id, invoices.estimate_id) as estimate_id, estimator.firstname as estimator_firstname, estimator.lastname as estimator_lastname, IFNULL(lead_statuses.lead_status_name, IFNULL(estimate_statuses.est_status_name, invoice_statuses.invoice_status_name)) as status_name, IFNULL(lead_statuses.lead_status_id, IFNULL(estimate_statuses.est_status_id, invoice_statuses.invoice_status_id)) as status_value, iestimates.estimate_balance', FALSE);
		$this->db->join('followup_settings', 'fu_fs_id = fs_id');
		$this->db->join('clients', 'followups.fu_client_id = clients.client_id', 'left');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('client_tags', 'clients.client_id = client_tags.client_id', 'left');
        $this->db->join('followup_settings_tags', 'followup_settings_tags.tag_id = client_tags.tag_id', 'left');
		$this->db->join('users', 'users.id = fu_author', 'left');
		$this->db->join('users estimator', 'estimator.id = fu_estimator_id', 'left');
		$this->db->join('leads', "leads.lead_id = fu_item_id AND fu_module_name = 'leads'", 'left');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->join('estimates', "estimates.estimate_id = fu_item_id AND fu_module_name = 'estimates'", 'left');
		$this->db->join('estimate_statuses', "estimates.status = estimate_statuses.est_status_id", 'left');
		$this->db->join('invoices', "invoices.id = fu_item_id AND fu_module_name = 'invoices'", 'left');
		$this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
		$this->db->join('estimates iestimates', "invoices.estimate_id = iestimates.estimate_id", 'left');
		
		$sort = 'ASC';
		if($status && $status != 'new')
			$sort = 'DESC';
		
		$this->db->order_by('fu_date', $sort);
		
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/
		//$this->db->group_by('fu_fs_id');
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/
		/***************FOR TEST ONLY**************************/

		if($limit && $offset !== FALSE)
			$this->db->limit($limit, $offset);

		return $this->db->get($this->_table)->result();
	}

	function get_list_count($status = NULL, $where = []) {
		if($status)
			$this->db->where('fu_status', $status);
		if($where)
			$this->db->where($where);
		$this->db->join('followup_settings', 'fu_fs_id = fs_id');
		return $this->db->count_all_results($this->_table);
	}
}
