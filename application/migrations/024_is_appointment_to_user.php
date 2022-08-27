<?php

class Migration_is_appointment_to_user extends CI_Migration {

    public function up() {
        
        $fields = array(
            'is_appointment' => array('type' => 'TINYINT', 'constraint' => 1,  'default' => 0)
        );

        $this->dbforge->add_column('users', $fields);
        
    }

    public function down() {
        $this->dbforge->drop_column('users', 'is_appointment');
    }

}