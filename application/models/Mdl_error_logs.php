<?php
class Mdl_error_logs extends JR_Model
{
	protected $_table = 'error_logs';
	protected $primary_key = 'el_id';


	public function __construct() {
		parent::__construct();
	}

	public function exist_error_log($hash){
	    $row = [];
	    $time = time();
	    $time = $time-7200;
	    $this->_database->_reset_select();
	    $query = $this->_database->get_where('error_logs', ['el_error_hash'=>$hash, 'el_cteated_time >' => $time], 1);
	    if($query)
	        $row = $query->row_array();

        if (isset($row) && !empty($row))
	        return true;

	    return false;
	}

	public function push_error_log($hash){
	    $data = ['el_error_hash'=>$hash, 'el_cteated_time'=>time()];
	    $this->_database->_reset_select();
	    $this->_database->insert('error_logs', $data);
	}
}