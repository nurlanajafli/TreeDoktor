<?php

class Migration_change_task_country extends CI_Migration {

    public function up() {
        
        $this->dbforge->drop_column('client_tasks', 'task_country');
        
        $fields = [
            'task_country' => [
                'type' => 'VARCHAR', 
                'constraint' => 50, 
                'null' => true,
            ],
        ];
        
        $this->dbforge->add_column('client_tasks', $fields, 'task_zip');
    }

    public function down() {
        $this->dbforge->drop_column('client_tasks', 'task_country');

        $fields = [
            'task_country' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ];
        
        $this->dbforge->add_column('client_tasks', $fields);

    }

}