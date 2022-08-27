<?php

class Migration_add_task_country extends CI_Migration {

    public function up() {
        $fields = [
            'task_country' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ];
        
        $this->dbforge->add_column('client_tasks', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('client_tasks', 'task_country');
    }

}