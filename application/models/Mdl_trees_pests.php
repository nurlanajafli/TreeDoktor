<?php

class Mdl_trees_pests extends JR_Model
{
	protected $_table = 'trees_pests';
	protected $primary_key = 'pest_id';
	
	
	public $has_many = array('products' => array('primary_key' => 'tpp_pest_id', 'model' => 'mdl_pests_products'));

	
	
	public function __construct() {
		parent::__construct();
		
	}

	function search_by_name($reff, $field)
	{
		$this->db->select("pest_id as id, " . $field . " as text, trees_pests.*", FALSE);
		$array = array(
			'pest_eng_name' => $reff,
			'pest_lat_name' => $reff
		);
		$this->db->or_like($array);
	//	$this->db->group_by('clients.client_id');
		return $this->db->get($this->_table);
	}
}
