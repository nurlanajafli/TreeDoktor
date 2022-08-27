<?php

class Migration_add_map_type extends CI_Migration {

    public function up() {
        $fields = array(
            'ti_map_type' => array(
                'type' => 'ENUM',
                'constraint' => ["map","image"],
                'default' => 'map'
            ),
        );
        
        $this->dbforge->add_column('tree_inventory', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('tree_inventory', 'ti_map_type');
    }

}
