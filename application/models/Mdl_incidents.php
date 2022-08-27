<?php

class Mdl_incidents extends JR_Model
{
	protected $_table = 'incidents';
	protected $primary_key = 'inc_id';
	protected $after_get = ['_decode_payload'];

	public function __construct() {
		parent::__construct();
	}

	protected function _decode_payload($incident) {
	    if(!$incident)
	        return FALSE;
	    $incident->inc_payload = $incident->inc_payload ? json_decode($incident->inc_payload) : [];
	    if(json_last_error() !== JSON_ERROR_NONE)
            $incident->inc_payload = [];
	    return $incident;
    }

	function getIncidentsList($limit = 0, $offset = 0) {
	    $this->db->select('incidents.*, workorders.workorder_no, users.firstname, users.lastname');
        $this->db->join('schedule', 'inc_job_id = schedule.id', 'left');
        $this->db->join('workorders', 'schedule.event_wo_id = workorders.id', 'left');
        $this->db->join('users', 'inc_user_id = users.id', 'left');
        $this->db->order_by('inc_created_at', 'DESC');
        if(intval($limit) === $limit && intval($offset) === $offset)
            $this->db->limit($limit, $offset);
        $data = $this->db->get('incidents')->result();
        foreach ($data as &$value)
            $value = $this->_decode_payload($value);

        return $data;
    }

    function getIncidentData($id) {
        $this->db->select('incidents.*, workorders.workorder_no, users.firstname, users.lastname');
        $this->db->join('schedule', 'inc_job_id = schedule.id', 'left');
        $this->db->join('workorders', 'schedule.event_wo_id = workorders.id', 'left');
        $this->db->join('users', 'inc_user_id = users.id', 'left');
        $this->db->order_by('inc_created_at', 'DESC');
        $this->db->where('inc_id', $id);
        $data = $this->db->get('incidents')->row();
        $data = $this->_decode_payload($data);
        return $data;
    }
}
