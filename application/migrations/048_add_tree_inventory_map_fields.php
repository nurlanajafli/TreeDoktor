<?php

class Migration_add_tree_inventory_map_fields extends CI_Migration {

    public function up() {
        $fields = array(
            'tim_width' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default'=>0
            ),
            'tim_height' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default'=>0
            ),
        );
        
        $this->dbforge->add_column('tree_inventory_map', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('tree_inventory_map', 'tim_width');
        $this->dbforge->drop_column('tree_inventory_map', 'tim_height');
    }

}