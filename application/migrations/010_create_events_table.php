<?php

class Migration_create_events_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'ev_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            
            'ev_event_id' => array(
                'type' => 'bigint',
                'constraint' => 20
            ),
            
            'ev_team_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            
            'ev_estimate_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            
            'ev_tailgate_safety_form' => array(
                'type' => 'text',
                'null' => TRUE,
            )
        ));

        $this->dbforge->add_field("`ev_start_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field("`ev_end_time` datetime DEFAULT NULL");

        $this->dbforge->add_key('ev_id', TRUE);
        $this->dbforge->create_table('events');
    }

    public function down() {
        $this->dbforge->drop_table('events', true);
    }

}