<?php

class Migration_add_read_notification_field extends CI_Migration {

    public function up() {
        $fields = array(
            'notification_read' => array('type' => 'TINYINT', 'constraint' => 1,  'default' => 0)
        );
        $this->dbforge->add_column('user_notifications', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('user_notifications', 'notification_read');
    }

}
