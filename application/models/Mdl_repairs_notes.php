<?php

class Mdl_repairs_notes extends JR_Model
{
	protected $_table = 'equipment_repair_notes';
	protected $primary_key = 'equipment_note_id';

	protected $before_get = array('joinData');

	public $belongs_to = array('repairs' => array('primary_key' => 'repair_id', 'model' => 'mdl_repairs'));
	//public $belongs_to = array();

	

	public function __construct() {
		parent::__construct();
		/*$this->_database->select("equipment_repair_notes.*, CONCAT(note_author.firstname, ' ', note_author.lastname) as note_name, note_author.picture", FALSE);
		$this->_database->join('users note_author', 'equipment_repair_notes.equipment_note_author = note_author.id', 'left');
		$this->_database->order_by('equipment_note_date');*/
	}

	function joinData() {
		$this->_database->select("equipment_repair_notes.*, CONCAT(note_author.firstname, ' ', note_author.lastname) as note_name, note_author.picture", FALSE);
		$this->_database->join('users note_author', 'equipment_repair_notes.equipment_note_author = note_author.id', 'left');
		$this->_database->order_by('equipment_note_date', 'DESC');
	}



	
	function get_with_authors()
	{
		$this->_database->select("equipment_repair_notes.*, CONCAT(note_author.firstname, ' ', note_author.lastname) as note_name", FALSE);
		$this->_database->join('users note_author', 'equipment_repair_notes.equipment_note_author = note_author.id', 'left');
		$result = $this->_database->get($this->_table)->result();
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		return $result;
	}
}
