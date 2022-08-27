<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Mdl_users_tracking extends MY_Model
{
    public $table = 'users_tracking';
	public $primary_key = 'ut_id';

	function __construct()
	{
		parent::__construct();		
	}
    
    function delete_where($wdata = []){
        if(!empty($wdata)){
            $this->db->where($wdata);
            $this->db->delete($this->table);
        }
    }
    
    function find_all_latest() {
               
        $this->db->select('ut.ut_user_id, ut.ut_id, ut.ut_date, ut.ut_lat, ut.ut_lng');
        $this->db->from('users_tracking ut');
        $this->db->join('(select ut_user_id, max(ut_date) as max_date from users_tracking group by ut_user_id) utj', 'utj.ut_user_id = ut.ut_user_id and ut.ut_date = utj.max_date');
        $this->db->join('emp_login el', "ut.ut_user_id = el.login_user_id AND el.login_date = '" . date('Y-m-d') . "' AND el.logout IS NULL");
        $this->db->where('ut.ut_date >=', date('Y-m-d 00:00:00'));
        $this->db->group_by(['ut.ut_date', 'ut.ut_lat', 'ut.ut_lng']);
        return $this->db->get()->result();
    }

    function insertBatch($data) {
        $this->db->insert_batch($this->table, $data);
	    return $this->db->insert_id();
    }
    
    public function find_all($wdata = array(), $order = '', $group = [])
	{
		if (!empty($wdata)) {
			$this->db->where($wdata);
		}
		if ($order) {
			$this->db->order_by($order);
		}
		if($group != [])
			$this->db->group_by($group);
		else
			$this->db->group_by(['ut_date', 'ut_lat', 'ut_lng']);
		return $this->db->get($this->table)->result();
	}
}
