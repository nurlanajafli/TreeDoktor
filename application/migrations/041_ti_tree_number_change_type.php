<?php

class Migration_ti_tree_number_change_type extends CI_Migration {

    public function up() {
        $fields = array(
            'ti_tree_number' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            )
        );
        $this->dbforge->modify_column('tree_inventory', $fields);
    }

    public function down() {
        $fields = array(
            'ti_tree_number' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            )
        );
        $this->dbforge->modify_column('tree_inventory', $fields);
    }
}