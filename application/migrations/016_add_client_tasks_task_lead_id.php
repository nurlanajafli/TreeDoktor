<?php

class Migration_add_client_tasks_task_lead_id extends CI_Migration {

    public function up() {
        $fields = array(
            'task_lead_id' => array('type' => 'INT', 'constraint' => 11,  'null' => TRUE)
        );
        $this->dbforge->add_column('client_tasks', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('client_tasks', 'task_lead_id');
    }

}