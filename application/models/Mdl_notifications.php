<?php
class Mdl_notifications extends JR_Model
{
	protected $_table = 'user_notifications';
	protected $primary_key = 'notification_id';
	
	public function __construct() {
		parent::__construct();
	}
}
