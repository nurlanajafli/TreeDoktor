<?php
class Mdl_users_management_orm extends JR_Model
{
	protected $_table = 'users_management';
	protected $primary_key = 'um_id';
	public function __construct() {
		parent::__construct();
	}

	//um_id, um_author_id, um_user_id, um_action, um_action_value, created_at

	public function add_user_event($data)
	{
		$data['um_author_id'] = $this->session->userdata['user_id'];
		return $this->insert($data);
	}

}
