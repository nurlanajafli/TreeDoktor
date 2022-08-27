<?php

class Mdl_users_orm extends JR_Model
{
	protected $_table = 'users';
	protected $primary_key = 'id';
	
	public $belongs_to = array('repair_notes' => array('primary_key' => 'equipment_note_author', 'model' => 'mdl_repairs_notes'));
	
	function __construct()
	{
		parent::__construct();
		
	}

	

}

//End model.
