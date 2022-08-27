<?php

class Migration_schedule_event_state extends CI_Migration {

    public function up() {
        
        $fields = array(
            'event_state' => array('type' => 'TINYINT', 'constraint' => 1,  'default' => 0)
        );

        $this->dbforge->add_column('schedule', $fields);
        
    }

    public function down() {
        $this->dbforge->drop_column('schedule', 'event_state');
    }
}