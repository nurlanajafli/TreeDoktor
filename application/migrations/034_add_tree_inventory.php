<?php

class Migration_add_tree_inventory extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'ti_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
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
            'ti_client_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'ti_tree_number' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'ti_tree_type' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'ti_tree_priority' => array(
                'type' => 'ENUM',
                'constraint' => ['low', 'middle', 'high'],
                'default' => 'low',
                'null' => FALSE
            ),
            'ti_prune_type_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'ti_remark'=>array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'ti_title' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'ti_size' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            )
        ));

        $this->dbforge->add_key('ti_id', TRUE);
        $this->dbforge->create_table('tree_inventory');
    }

    public function down() {
        $this->dbforge->drop_table('tree_inventory', true);
    }

}
