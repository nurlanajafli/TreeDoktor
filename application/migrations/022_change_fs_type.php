<?php

class Migration_change_fs_type extends CI_Migration {

    public function up() {
         $fields = array(
            'fs_type' => array(
                'type' => 'ENUM',
                'constraint' => ['call', 'email', 'sms', 'mail', 'equipment_alarm', 'expired_user_docs'],
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('followup_settings', $fields);
        

    }

    public function down() {
         $fields = array(
            'fs_type' => array(
                'type' => 'ENUM',
                'constraint' => ['call', 'email', 'sms', 'mail'],
                'null' => TRUE
            )
        );

        
        $this->dbforge->modify_column('followup_settings', $fields);
    }

}
