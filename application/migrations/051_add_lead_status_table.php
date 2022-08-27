<?php

class Migration_add_lead_status_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'lead_status_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
			'lead_status_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			),
			'lead_status_active' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			),
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
        ));
        $this->dbforge->add_key('lead_status_id', TRUE);
        
        $this->dbforge->create_table('lead_statuses');
    }

    public function down() {
        $this->dbforge->drop_table('lead_statuses', true);
    }

}
