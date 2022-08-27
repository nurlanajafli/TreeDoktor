<?php

class Migration_add_lead_id extends CI_Migration {

    public function up() {
         $fields = array(
            'ti_lead_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'AFTER'=>'ti_client_id'
            )
        );

        $this->dbforge->add_column('tree_inventory', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('tree_inventory', 'ti_lead_id');
    }

}