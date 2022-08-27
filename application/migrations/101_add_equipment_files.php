<?php

class Migration_add_equipment_files extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'file_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'file_eq_item_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'file_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ),
            'file_path' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ),
            'file_exp' => array(
                'type' => 'date', 
                'null' => TRUE
            ),
            'file_notification' => array(
				'type' => 'TINYINT',
				'constraint' => 1,				
				'default' => 0,
				'null' => FALSE
			),
			'file_notification_user' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            )
        ));
        $this->dbforge->add_key('file_id', TRUE);
        $this->dbforge->create_table('equipment_files');
        $sql = "CREATE INDEX file_eq_item_id ON equipment_files(file_eq_item_id)";
        $this->db->query($sql);
        $sql = "CREATE INDEX file_notification_user ON equipment_files(file_notification_user)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('equipment_files');
    }

}
