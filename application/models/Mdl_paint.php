<?php

class Mdl_paint extends JR_Model
{
	protected $_table = 'paint';
	protected $primary_key = 'paint_id';

	function __construct()
	{
		parent::__construct();
	}

	function updateBatchByPaths($data) {
	    if(!empty($data))
            $this->db->update_batch($this->_table, $data, 'paint_path');
        return TRUE;
    }
}
