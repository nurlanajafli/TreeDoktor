<?php

class Migration_tree_inventory_map extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'tim_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'tim_client_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'tim_lead_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'tim_image' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ),
            'tim_file_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            )
        ));
        $this->dbforge->add_key('tim_id', TRUE);
        $this->dbforge->create_table('tree_inventory_map');
    }

    public function down() {
        $this->dbforge->drop_table('tree_inventory_map', true);
    }

}