<?php

class Mdl_trees_relations extends JR_Model
{
	protected $_table = 'tree_pest_relations';
	protected $primary_key = 'tpr_id';
	
	
	public $belongs_to = array('pests' => array('primary_key' => 'tpr_pest_id', 'model' => 'mdl_trees_pests'));
	//public $belongs_to = array('trees' => array('primary_key' => 'tpr_tree_id', 'model' => 'mdl_trees'));
	//public $before_get = array('get_products');
	
	
	public function __construct() {
		parent::__construct();
		
	}
	
	function get_products($where)
	{
		$this->_database->select("tree_pest_relations.*, trees_pests_products.*", FALSE);
		$this->_database->join('trees_pests_products', 'tree_pest_relations.tpr_pest_id = trees_pests_products.tpp_pest_id', 'left');
		
		
		$this->_database->where($where);
		
		$result = $this->_database->get($this->_table)->result();
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		return $result;
	}
	
}
