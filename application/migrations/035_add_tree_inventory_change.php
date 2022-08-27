<?php

class Migration_add_tree_inventory_change extends CI_Migration {

    public function up() {
        $fields = array(
            'ti_lat' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'ti_lng' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('tree_inventory', $fields);
    }

    public function down() {
        $fields = array(
            'ti_lat' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'ti_lng' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('tree_inventory', $fields);
    }

}