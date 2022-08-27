<?php
class Mdl_services_orm extends JR_Model
{
	protected $_table = 'estimates_services';
	protected $primary_key = 'id';
	
	public $belongs_to = array('mdl_estimates_orm' => array('primary_key' => 'estimate_id', 'model' => 'mdl_estimates_orm'));

	public function __construct() {
		parent::__construct();
	}
	
	function delete_full_service($where)
	{
		foreach($where as $key=>$val)
		{

			$this->_database->from($this->_table);
			$this->_database->where($this->primary_key, $val);
			$this->_database->delete();
		}
	}
	
	function get_service_status()
	{
		$query = $this->_database->get('estimates_services_status');
		return $query->result_array();
	}
	
	function save_status($data, $id = NULL)
	{
		if(!$id)
		{
			$this->_database->insert('estimates_services_status', $data);
			$result = $this->_database->insert_id();
		}
		else
			$result = $this->_database->where('services_status_id', $id)->set($data)->update('estimates_services_status');
		return $result;
	}
	
	function delete_status($id)
	{
		$this->_database->where('services_status_id', $id);
		$result = $this->_database->delete('estimates_services_status');
		return $result;
	}
}
