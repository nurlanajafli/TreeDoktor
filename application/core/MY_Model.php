<?php

class MY_Model extends CI_Model
{

	public $table;
	public $primary_key;

	//Checks if record exists. Returns TRUE or FALSE.
	public function check_by_id($id)
	{

		$this->db->where($this->primary_key, $id);
		$query = $this->db->get($this->table);

		if ($query->num_rows() == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

//*******************************************************************************************************************
//*************
//*************						Retrieves a single record based on primary key value;
//*************
//*******************************************************************************************************************	
	// Retrieves a single record based on primary key value.
	public function find_by_id($id)
	{
		return $row = $this->db->where($this->primary_key, $id)->limit(1)->get($this->table)->row();
	}

	public function find_by_fields($wdata)
	{
		return $row = $this->db->where($wdata)->limit(1)->get($this->table)->row();
	}

	public function find_by_fields_join($wdata, $join=[]){
		$this->db->where($wdata);
		$this->db->limit(1);

		if(isset($join[0]) && isset($join[1])){
			$type = element(2, $join, 'inner');
			$this->db->join($join[0], $join[1], $type);
		}

		return $this->db->get($this->table)->row();
	}
//end find_by_id;
//*******************************************************************************************************************
//*************
//*************						Retrieves ALL records in the table;
//*************
//*******************************************************************************************************************		
	// Retrieves an table as an object.
	public function find_all($wdata = array(), $order = '')
	{
		if (!empty($wdata)) {
			$this->db->where($wdata);
		}
		if ($order) {
			$this->db->order_by($order);
		}
		return $this->db->get($this->table)->result();
	}
	// end find_all;

	// Retrieves an table as an object with search keyword and limit.
	public function find_all_with_limit($search_array = array(), $limit = '', $start = '', $order = '', $wdata = array())
	{
		if (!empty($wdata) && $wdata)
			$this->db->where($wdata);
		if ($limit != '') {
			$this->db->limit($limit, $start);
		}

		if (!empty($search_array)) {
			$this->db->or_like($search_array);
		}

		if ($order) {
			$this->db->order_by($order);
		}

		return $this->db->get($this->table)->result();
	}
	// end find_all_with_limit;

	//get all record count in a table
	public function record_count($or_wdata = array(), $wdata = array())
	{

		$this->db->from($this->table);

		if (!empty($or_wdata)) {
			$this->db->or_like($or_wdata);
		}

		if (!empty($wdata)) {
			$this->db->where($wdata);
		}

		return $num_results = $this->db->count_all_results();

		//print($this->db->last_query());

	}//end record_count

	// Retrieves an table as an object with search  limit.
	public function find_all_with_limit_order($order = '', $limit = '', $start = '')
	{

		if ($limit != '') {
			$this->db->limit($limit, $start);
		}

		if (!empty($order)) {
			$this->db->order_by($order);
		}

		return $this->db->get($this->table)->result();
	}
	// end find_all_with_limit;

	//get all record count in a table
	public function record_count_order($order = '')
	{

		$this->db->from($this->table);

		if (!empty($order)) {
			$this->db->order_by($order);
		}
		return $num_results = $this->db->count_all_results();

		//print($this->db->last_query());

	}//end record_count

	// Deletes a record based on primary key value.
	public function delete($id)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->delete($this->table))
			return TRUE;
	}//end delete function

	// Update record on primary key value
	public function update($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}//end update function

	// Insert record
	public function insert($data)
	{
		if ($this->db->insert($this->table, $data))
			return $this->db->insert_id();
		return FALSE;
	}

	//end insert function

	function getCounter($wdata = array())
	{
		return $this->record_count($or_wdata = array(), $wdata);
	}

	function get_all_in($field, $wdata, $where_in)
	{
		if(!empty($wdata))
			$this->db->where($wdata);
		if(!empty($where_in))
			$this->db->where_in($field, $where_in);
		$query = $this->db->get($this->table);
		if($query)
			return $query->result_array();
		return FALSE;
	}

	function uploadFile($dir, $name, $field = 'file', $types = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF', $overwrite = TRUE, $ext = FALSE)
	{
		if (!isset($_FILES[$field]))
			return FALSE;
        /*$dir = rtrim($dir, '/');
        $folders = explode('/', $dir);

        $path = FCPATH . 'uploads/';
        @chmod($path, 0777);
        foreach ($folders as $folder) {
            $path .= $folder . '/';
            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
        }*/
        $path = 'uploads/' . $dir;
		if(!$ext)
			$ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
		if ($types)
			$config['allowed_types'] = $types;
		$config['overwrite'] = $overwrite;
		$this->load->library('upload');
		$config['upload_path'] = $path;
		$config['file_name'] = $name;
		if ($ext)
			$config['file_name'] .= '.' . $ext;
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($field))
			return FALSE;
		else {
			$uploadData = $this->upload->data();
			$file['filepath'] = 'uploads/' . trim($dir, '/') . '/' . $uploadData['file_name'];
			$file['filename'] = $uploadData['file_name'];
			return $file;
		}
	}

	function status_log($data)
	{
		$user_id = intval($this->session->userdata('user_id'));
		$data['status_user_id'] = 0;
		if($user_id)
			$data['status_user_id'] = $user_id;
		if ($this->db->insert('status_log', $data))
			return TRUE;
		return FALSE;
	}
	
	function get_status_log($where, $where_in = array())
	{
		$this->db->where($where);
		if(!empty($where_in))
			$this->db->where_in('status_value', $where_in);
		$query = $this->db->get('status_log');
		if($query)
			return $query->result_array();
		return FALSE;
	}
	
	function get_status_full_data($where, $where_in = array(), $table = false, $field = false, $count = false)
	{
		$this->db->where($where);
		if(!empty($where_in))
			$this->db->where_in('status_value', $where_in);
		if($table && $field)
		{
			$this->db->join($table, $table .'.'. $field . ' = '.' status_log.status_item_id', 'left');
			$this->db->join('clients', 'clients.client_id = ' . $table .'.client_id' , 'left');
		}
		$query = $this->db->get('status_log');
		//var_dump($this->db->last_query()); die;
		if($count)
			return $query->num_rows();
		if($query)
			return $query->result_array();
		return FALSE;
	}

}

//End model.
