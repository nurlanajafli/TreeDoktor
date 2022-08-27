<?php

class Migration_add_work_type extends CI_Migration {

    public function up() {
        $fields = array(
            'ti_work_type' => [
                'type' => 'JSON',
                'null' => TRUE,
            ]
        );

        $this->dbforge->add_column('tree_inventory', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('tree_inventory', 'ti_work_type');
    }

}