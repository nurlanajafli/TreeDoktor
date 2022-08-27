<?php

class Migration_add_user_certificates extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'us_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'us_user_id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => TRUE
            ),
            'us_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => FALSE
            ),
            'us_photo' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE
            ),
            'us_exp' => array(
                'type' => 'date', 
                'null' => FALSE
            ),
            'us_notification' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => TRUE
			),
        ));
        $this->dbforge->add_key('us_id', TRUE);
        $this->dbforge->create_table('user_certificates');
    }

    public function down() {
        $this->dbforge->drop_table('user_certificates', true);
    }

}
