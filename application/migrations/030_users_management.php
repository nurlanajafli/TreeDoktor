<?php

class Migration_users_management extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'um_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'um_author_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'um_user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'um_action' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                //'default' => NULL
            ),
            'um_action_value' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                //'default' => NULL
            ),
        ));
        
        $this->dbforge->add_field("`notification_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_key('um_id', TRUE);

        $this->dbforge->create_table('users_management');
    }

    public function down() {
        $this->dbforge->drop_table('users_management', true);
    }

}