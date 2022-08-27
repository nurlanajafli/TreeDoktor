<?php

class Migration_tree_inventory_work_types extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'tiwt_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'tiwt_tree_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'tiwt_work_type_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
        ));
        $this->dbforge->add_key('tiwt_id', TRUE);
        $this->dbforge->create_table('tree_inventory_work_types');
    }

    public function down() {
        $this->dbforge->drop_table('tree_inventory_work_types', true);
    }

}