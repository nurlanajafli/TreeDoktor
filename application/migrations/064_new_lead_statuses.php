<?php

class Migration_new_lead_statuses extends CI_Migration {

    public function up() {
        $data = array( 
						array('lead_status_id' => 1, 'lead_status_name' => 'New', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 1, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 0),
						array('lead_status_id' => 2, 'lead_status_name' => 'For Approval', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 0, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 1),
						array('lead_status_id' => 3, 'lead_status_name' => 'No Go', 'lead_status_active' => 1, 'lead_status_declined' => 1, 'lead_status_default' => 0, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 0),
						array('lead_status_id' => 4, 'lead_status_name' => 'Estimated', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 0, 'lead_status_estimated' => 1, 'lead_status_for_approval' => 0)
					);
		foreach($data as $k=>$v)
			$insert = $this->db->insert('lead_statuses', $v);
        
        $data = array( 
						array('reason_id' => 1, 'reason_name' => "Don't provide this service", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 2, 'reason_name' => "Out of service area", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 3, 'reason_name' => "Don't want work done anymore", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 4, 'reason_name' => "Already Done", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 5, 'reason_name' => "Dublicate lead", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 6, 'reason_name' => "Hydro", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 7, 'reason_name' => "Dangerous tree no access", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 8, 'reason_name' => "Spam", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 9, 'reason_name' => "Already hired someone else", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 10, 'reason_name' => "The lead is not responding", 'reason_lead_status_id' => 3, 'reason_active' => 1)
					);
		foreach($data as $k=>$v)
			$insert = $this->db->insert('lead_reason_status', $v);
		$fields = array(
            'lead_status_id' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'null' => FALSE,
            ),
            'lead_reason_status_id' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'null' => FALSE,
            ),
        );
        $this->dbforge->add_column('leads', $fields);
        
		$this->db->query('update leads l join lead_statuses s on l.lead_status = s.lead_status_name set l.lead_status_id = s.lead_status_id');
		$this->db->query('update leads l join lead_reason_status s on l.lead_reason_status = s.reason_name set l.lead_reason_status_id = s.reason_id');
    }

    public function down() {
        $this->dbforge->drop_column('leads', 'lead_status_id');
        $this->dbforge->drop_column('leads', 'lead_reason_status_id');
        $data = array( 
						array('lead_status_id' => 1, 'lead_status_name' => 'New', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 1, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 0),
						array('lead_status_id' => 2, 'lead_status_name' => 'For Approval', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 0, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 1),
						array('lead_status_id' => 3, 'lead_status_name' => 'No Go', 'lead_status_active' => 1, 'lead_status_declined' => 1, 'lead_status_default' => 0, 'lead_status_estimated' => 0, 'lead_status_for_approval' => 0),
						array('lead_status_id' => 4, 'lead_status_name' => 'Estimated', 'lead_status_active' => 1, 'lead_status_declined' => 0, 'lead_status_default' => 0, 'lead_status_estimated' => 1, 'lead_status_for_approval' => 0)
					);
		
		foreach($data as $k=>$v)
		{
			$this->db->where(['lead_status_id' => $v['lead_status_id']]);
			$this->db->delete('lead_statuses');
		}
		$data = array( 
						array('reason_id' => 1, 'reason_name' => "Don't provide this service", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 2, 'reason_name' => "Out of service area", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 3, 'reason_name' => "Don't want work done anymore", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 4, 'reason_name' => "Already Done", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 5, 'reason_name' => "Dublicate lead", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 6, 'reason_name' => "Hydro", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 7, 'reason_name' => "Dangerous tree no access", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 8, 'reason_name' => "Spam", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 9, 'reason_name' => "Already hired someone else", 'reason_lead_status_id' => 3, 'reason_active' => 1),
						array('reason_id' => 10, 'reason_name' => "The lead is not responding", 'reason_lead_status_id' => 3, 'reason_active' => 1)
					);
		foreach($data as $k=>$v)
		{
			$this->db->where(['reason_id' => $v['reason_id']]);
			$this->db->delete('lead_reason_status');
		}
    }

}
