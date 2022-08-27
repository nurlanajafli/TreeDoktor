<?php

class Migration_add_null_task_address extends CI_Migration {

    public function up() {
        
        $fields = array(
            'task_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ),
            'task_city' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ),
            'task_state' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ),
            'task_zip' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => TRUE,
            ),
            'task_country' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('client_tasks', $fields);

    }

    public function down() {
        $fields = array(
            'task_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE,
            ),
            'task_city' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE,
            ),
            'task_state' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE,
            ),
            'task_zip' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => FALSE,
            ),
            'task_country' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('client_tasks', $fields);
    }

}