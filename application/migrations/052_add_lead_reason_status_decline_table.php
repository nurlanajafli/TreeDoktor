<?php

class Migration_add_lead_reason_status_decline_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'reason_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
			'reason_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			),
			'reason_lead_status_id' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			),
			'lead_status_confirmed' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			)
        ));
        $this->dbforge->add_key('reason_id', TRUE);
        $this->dbforge->create_table('lead_reason_status');
    }

    public function down() {
        $this->dbforge->drop_table('lead_reason_status', true);
    }

}
