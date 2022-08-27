<?php

class Migration_user_notifications extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'notification_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'notification_user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'notification_title' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
            ),
            'notification_message' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'notification_screen' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
            ),
            'notification_params' => array(
                'type' => 'JSON',
                'null' => FALSE
            )
        ));

        $this->dbforge->add_field("`notification_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field("`notification_deleted_at` datetime NULL DEFAULT NULL");

        $this->dbforge->add_key('notification_user_id');
        $this->dbforge->add_key('notification_id', TRUE);
        $this->dbforge->create_table('user_notifications');
    }

    public function down() {
        $this->dbforge->drop_table('user_notifications', true);
    }

}