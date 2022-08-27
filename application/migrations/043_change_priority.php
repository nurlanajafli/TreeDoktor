<?php

class Migration_change_priority extends CI_Migration {

    public function up() {
        
        $fields = array(
            'ti_tree_priority' => array(
                'type' => 'ENUM',
                'constraint' => ["low","medium","high"],
                'default' => 'low',
                'null' => FALSE
            ),
        );
        $this->dbforge->modify_column('tree_inventory', $fields);
        $this->db->where(['ti_tree_priority'=>'middle']);
        $this->db->or_where(['ti_tree_priority'=>'']);
        $this->db->update('tree_inventory', ['ti_tree_priority'=>'medium']);
    }

    public function down() {
        $fields = array(
            'ti_tree_priority' => array(
                'type' => 'ENUM',
                'constraint' => ["low","middle","high"],
                'default' => 'low',
                'null' => FALSE
            ),
        );
        $this->dbforge->modify_column('tree_inventory', $fields);

        $this->db->where(['ti_tree_priority'=>'medium']);
        $this->db->or_where(['ti_tree_priority'=>'']);
        $this->db->update('tree_inventory', ['ti_tree_priority'=>'middle'], ['ti_tree_priority'=>'medium']);

    }

}
