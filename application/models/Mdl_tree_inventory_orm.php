<?php
class Mdl_tree_inventory_orm extends JR_Model
{
	protected $_table = 'tree_inventory';
	protected $primary_key = 'ti_id';
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimate_id', 'model' => 'estimates/mdl_services_orm'));
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	
    //public $has_many = array( 'comments' );

	//Mdl_tree_inventory_work_types_orm
	
	public $has_many = [
		'work_types' => ['primary_key' => 'tiwt_tree_id', 'model' => 'mdl_tree_inventory_work_types_orm']
	];

    public $belongs_to = array(
    	'work_type' => array('primary_key' => 'ti_prune_type_id', 'model' => 'mdl_work_types_orm'),
    	'tree_type' => array('primary_key'=>'ti_tree_type', 'model'=>'mdl_trees'),
    );

    public $priority_color = ['low'=>'#7ec322', 'medium'=>'#f0ad4e', 'high'=>'#f04e4e'];

    public $validation_errors = [];
	public $validate = [
        
        ['field' => 'ti_lat', 'label' => 'Address', 'rules' => 'required'],
        ['field' => 'ti_lng', 'label' => 'Address', 'rules' => 'required'],
        ['field' => 'ti_client_id', 'label' => 'Client', 'rules' => 'required|numeric'],
        ['field' => 'ti_lead_id', 'label' => 'Lead', 'rules' => 'is_natural_no_zero'],
		['field' => 'ti_tree_number', 'label' => 'Tree #', 'rules' => 'required'],

        /*
        ['field' => 'ti_tree_type', 'label' => 'Tree Type', 'rules' => 'required|numeric'],
        
        ['field' => 'ti_tree_priority', 'label' => 'Tree Priority', 'rules' => 'required'],
		['field' => 'ti_prune_type_id', 'label' => 'Prune Type', 'rules' => 'required']
		*/
    ];

	public function __construct() {
		parent::__construct();
	}
	

	
	function save($data, $id=false, $skip_validation = false)
	{
		$valid = $this->unique_number($data, $id);
		$this->form_validation->set_message('is_natural_no_zero', '%s is required. Please select and try again');

		if($valid==FALSE)
			return $valid;

		if(!$id)
			$result = $this->insert($data);
		else{
			$result = $this->update($id, $data, $skip_validation);
		}

		if($result===FALSE){
			$this->validation_errors = array_merge($this->form_validation->error_array(), $this->validation_errors);
		}

		return $result;
	}

	function unique_number($data, $id) {
		if(!isset($data['ti_tree_number']) || !$data['ti_tree_number'] || !isset($data['ti_client_id']) || !$data['ti_client_id'] || !isset($data['ti_lead_id']) || !$data['ti_lead_id'] || !$data['ti_map_type'])
			return TRUE;

		if($id)
			$result = $this->get_by(['ti_tree_number'=>$data['ti_tree_number'], 'ti_client_id'=>$data['ti_client_id'], 'ti_lead_id'=>$data['ti_lead_id'], 'ti_map_type'=>$data['ti_map_type'], 'ti_id <>'=>$id]);
		else
			$result = $this->get_by(['ti_tree_number'=>$data['ti_tree_number'], 'ti_client_id'=>$data['ti_client_id'], 'ti_lead_id'=>$data['ti_lead_id'], 'ti_map_type'=>$data['ti_map_type'] ]);

		if(is_object($result) && empty($result))
		{
			$this->validation_errors['ti_tree_number'] = 'The Tree # field must contain a unique value.';
			return FALSE;
		}

		return TRUE;
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

	public function priority_color($priority){
		if(isset($this->priority_color[$priority]))
			return $this->priority_color[$priority];
		
		return $this->priority_color['middle'];
	}
}
