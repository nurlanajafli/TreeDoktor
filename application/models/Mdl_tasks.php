<?php

class Mdl_tasks extends MY_Model
{

	public function __construct()
	{
		// model constructor
		parent::__construct();
		$this->table = 'tasks';
	}

	// Get all active tasks;
	function getNextTodo($wdata = '')
	{

		// Preset the query;
		$this->db->select('tasks.*, us.firstname as to_firstname, us.lastname as to_lastname, users.id, users.firstname, users.lastname');
		$this->db->from('tasks', 'users');
		$this->db->join('users', 'tasks.task_created_by = users.id');
		$this->db->join('users us', 'tasks.user_id = us.id', 'left');
		$this->db->order_by("tasks.task_date_created", "desc");

		// Check if array is not empty;
		if ($wdata != '') {
			$this->db->where($wdata);
		}

		//Query the db;
		$query = $this->db->get();
		//Debug;
		//print_r($query->result());exit;

		//Return the result();
		//if ($query->num_rows() > 0) {
			return $query->result();
		/*} else {
			return FALSE;
		}*/
	}// end getNextTodo;

	//Get all completed tasks;
	function getCompletedTodo($wdata = '')
	{

		// Preset the query;
		$this->db->select('tasks.*, us.firstname as to_firstname, us.lastname as to_lastname, users.id, users.firstname, users.lastname');
		$this->db->from('tasks', 'users');
		$this->db->join('users', 'tasks.task_created_by = users.id');
		$this->db->join('users us', 'tasks.user_id = us.id', 'left');
		$this->db->order_by("tasks.task_date_created", "desc");

		// Check if array is not empty;
		if ($wdata != '') {
			$this->db->where($wdata);
		}

		//Query the db;
		$query = $this->db->get();

		//Debug;
		//print_r($query->result());exit;

		//Return the result();
		//if ($query->num_rows() > 0) {
			return $query->result();
		/*} else {
			return FALSE;
		}*/
	}

	// end getCompletedTodo

	function add($data)
	{
		$this->db->insert('tasks', $data);
	}

	function get($id)
	{
		$query = $this->db->get_where('tasks', array('task_id' => $id));
		if ($query->num_rows() == 0) {
			return false;
		}
		$result = $query->result();
		return isset($result[0])?$result[0]:[];
	}

	function delete($id)
	{
		$query = $this->db->get_where('tasks', array('task_id' => $id));

		if ($query->num_rows() == 0) {
			return false;
		} else {
			$this->db->delete('tasks', array('task_id' => $id));
			return true;
		}
	}

	function setComplete($id)
	{
		$query = $this->db->get_where('tasks', array('task_id' => $id));

		if ($query->num_rows() == 0) {
			return false;
		} else {
			$this->db->update('tasks', array('task_status' => 0), array('task_id' => $id));
			return true;
		}
	}

	function assignTask($updateData, $wdata)
	{

		return $this->db->update($this->table, $updateData, $wdata);
	}

	function updateTask($updateData, $wdata)
	{

		return $this->db->update($this->table, $updateData, $wdata);
	}
}

?>
