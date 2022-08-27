<?php

class Migration_add_tree_inventory_fields extends CI_Migration {

    public function up() {
        
       

        $fields = array(
            'ti_file' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'ti_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'ti_stump_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
        );

        $this->dbforge->add_column('tree_inventory', $fields);

        
    }

    public function down() {
        
        $this->dbforge->drop_column('tree_inventory', 'ti_file');
        $this->dbforge->drop_column('tree_inventory', 'ti_cost');
        $this->dbforge->drop_column('tree_inventory', 'ti_stump_cost');
    }

}