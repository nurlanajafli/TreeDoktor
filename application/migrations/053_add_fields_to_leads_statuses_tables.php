<?php

class Migration_add_fields_to_leads_statuses_tables extends CI_Migration {

    public function up() {
        $fields = array(
            'lead_status_estimated' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 0,
			), 
            'lead_status_for_approval' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 0,
			)
        );
        $this->dbforge->add_column('lead_statuses', $fields);
        $fields = array(
            'lead_status_declined' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 0,
			), 
            'lead_status_default' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 0,
			)
        );
        $this->dbforge->modify_column('lead_statuses', $fields);
        
        $this->dbforge->drop_column('lead_reason_status', 'lead_status_confirmed');
        $fields = array(
            'reason_active' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			)
        );
        $this->dbforge->add_column('lead_reason_status', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('lead_statuses','lead_status_estimated');
        $this->dbforge->drop_column('lead_statuses', 'lead_status_for_approval');
        $fields = array(
            'lead_status_declined' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			), 
            'lead_status_default' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			)
        );
        $this->dbforge->modify_column('lead_statuses', $fields);
        $this->dbforge->drop_column('lead_reason_status', 'reason_active');
        $fields = array(
            'lead_status_confirmed' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			)
        );
        $this->dbforge->add_column('lead_reason_status', $fields);
    }
}
