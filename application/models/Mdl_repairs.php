<?php

class Mdl_repairs extends JR_Model
{
	protected $_table = 'repair_requests';
	protected $primary_key = 'repair_id';

	public $has_many = array('repair_notes' => array('primary_key' => 'equipment_repair_id', 'model' => 'mdl_repairs_notes'));
	
	
	public function __construct() {
		parent::__construct();
		$this->load->model('mdl_equipments');
	}

	

	function get_all_data($where = array(), $limit = NULL, $offset = NULL, $order = array(), $or_where = array())
	{
		$this->_database->select("repair_requests.*,  equipment_items.*, CONCAT(solder.firstname, ' ', solder.lastname) as solder_name, CONCAT(repair_author.firstname, ' ', repair_author.lastname) as author_name", FALSE);//, //equipment_repair_notes.*,  CONCAT(note_author.firstname, ' ', note_author.lastname) as note_name", FALSE);
		//$this->_database->join('equipment_repair_notes', 'repair_requests.repair_id = equipment_repair_notes.equipment_repair_id');
		$this->_database->join('equipment_items', 'repair_requests.repair_item_id = equipment_items.item_id');
		$this->_database->join('users solder', 'repair_requests.repair_solder_id = solder.id', 'left');
		$this->_database->join('users repair_author', 'repair_requests.repair_author_id = repair_author.id', 'left');
		//$this->_database->join('users note_author', 'equipment_repair_notes.equipment_note_author = note_author.id', 'left');
		
		if((is_array($where) && count($where)) || strlen($where))
			$this->_database->where($where);
		if((is_array($or_where) && count($or_where)) || strlen($or_where))
			$this->db->or_where($or_where);
		
		if(!empty($order) && $order)
		{
			foreach($order as $k=>$v)
				$this->_database->order_by($k, $v);
		}
		else		
			$this->_database->order_by('repair_date', 'DESC');
		if($limit)
			$this->_database->limit($limit, $offset);
		if($limit == 1)
			$result = $this->_database->get($this->_table)->row();
		else
			$result = $this->_database->get($this->_table)->result();
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		$this->_database->with = array();
		return $result;
	}

	function get_count($wdata = []) {
		$this->db->where($wdata);
		return $this->db->count_all_results($this->_table);
	}
	
	

}
