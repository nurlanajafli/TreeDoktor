<?php

class Migration_add_suspended_status_to_users extends CI_Migration {

    public function up() {
        $fields = array(
            'active_status' => array(
                'type' => 'ENUM',
                'constraint' => ["yes","no","suspended"],
                'default' => 'no',
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('users', $fields);
    }

    public function down() {
        $fields = array(
            'active_status' => array(
                'type' => 'ENUM',
                'constraint' => ["yes","no"],
                'default' => 'no',
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('users', $fields);
    }

}
