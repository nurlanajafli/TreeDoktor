<?php
class Mdl_tree_inventory_map_orm extends JR_Model
{
	protected $_table = 'tree_inventory_map';
	protected $primary_key = 'tim_id';
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimate_id', 'model' => 'estimates/mdl_services_orm'));
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	
    //public $has_many = array( 'comments' );

	//Mdl_tree_inventory_work_types_orm
	/*
	public $has_many = [
		'work_types' => ['primary_key' => 'tiwt_tree_id', 'model' => 'mdl_tree_inventory_work_types_orm']
	];
	*/
	/*
    public $belongs_to = array(
    	'work_type' => array('primary_key' => 'ti_prune_type_id', 'model' => 'mdl_work_types_orm'),
    	'tree_type' => array('primary_key'=>'ti_tree_type', 'model'=>'mdl_trees'),
    );
	*/
    public $validation_errors = [];
	public $validate = [
        /*
        ['field' => 'ti_lat', 'label' => 'Address', 'rules' => 'required'],
        ['field' => 'ti_lng', 'label' => 'Address', 'rules' => 'required'],
        ['field' => 'ti_client_id', 'label' => 'Client', 'rules' => 'required|numeric'],
        ['field' => 'ti_lead_id', 'label' => 'Lead', 'rules' => 'required|is_natural_no_zero'],
		['field' => 'ti_tree_number', 'label' => 'Tree #', 'rules' => 'required'],
		*/
    ];

	public function __construct() {
		parent::__construct();
	}
	


	function save($data, $id=false)
	{
		$map = $this->get_by(['tim_client_id'=>$data['tim_client_id'], 'tim_lead_id'=>$data['tim_lead_id']]);
		
		if(empty($map) || !$map->tim_id)
			$result = $this->insert($data);
		else{
			$result = $this->update($id, $data);
		}
		
		return $result;
	}


	public function fields()
	{
	    $keys = array();
	    if ($this->validate)
	    {
	        foreach ($this->validate as $key)
	        {
	            $keys[] = $key['field'];
	        }
	    }
	    return $keys;
	}

	public function primary_key()
	{
		return $this->primary_key;
	}
}
