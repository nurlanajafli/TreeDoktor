<?php
class Mdl_tree_inventory_work_types_orm extends JR_Model
{
	protected $_table = 'tree_inventory_work_types';
	protected $primary_key = 'tiwt_id';
	
	public $belongs_to = array(
    	'work_type' => array('primary_key' => 'tiwt_work_type_id', 'model' => 'mdl_work_types_orm'),
    	/*
    	'tree_type' => array('primary_key'=>'ti_tree_type', 'model'=>'mdl_trees')
    	*/
    );

    public $validation_errors = [];
	

	public function __construct() {
		parent::__construct();
	}

	public function sync($tiwt_tree_id, $types){

		if(!$tiwt_tree_id)
			return FALSE;

		$this->delete_by(['tiwt_tree_id'=>$tiwt_tree_id]);
		
		if(!$types || empty($types))
			return FALSE;

		$data = [];
		foreach ($types as $key => $value) {
			$data[] = ['tiwt_tree_id'=>$tiwt_tree_id, 'tiwt_work_type_id'=>$value];	
		}

		$ids = $this->insert_many($data, FALSE);
		if(empty($ids))
			return FALSE;

		return $ids;
	}
}
	
