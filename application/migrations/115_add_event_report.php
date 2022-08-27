<?php

class Migration_add_event_report extends CI_Migration {

    public function up() {
        
        
        $this->dbforge->add_field([
            'er_id'         => ['type' => 'BIGINT', 'constraint' => 20, 'auto_increment' => TRUE],
            'er_event_id'   => ['type' => 'BIGINT', 'constraint' => 20, 'null' => TRUE],
            'er_estimate_id'=> ['type' => 'BIGINT', 'constraint' => 20, 'null' => TRUE],
            'er_team_id'    => ['type' => 'BIGINT', 'constraint' => 20, 'null' => TRUE],
            'er_wo_id'         => ['type' => 'BIGINT', 'constraint' => 20, 'null' => TRUE],
            'er_event_payment' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
            'er_event_payment_type'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
            'er_event_work_remaining'  => ['type' => 'TEXT', 'null' => TRUE],
            'er_event_damage'          => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
            'er_event_damage_description'  => ['type' => 'TEXT', 'null' => TRUE],
            'er_event_description'         => ['type' => 'TEXT', 'null' => TRUE],
            'er_malfunctions_equipment'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
            'er_expenses'                  => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
            'er_expenses_description'      => ['type' => 'TEXT', 'null' => TRUE],
            'er_payment_amount'            => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'er_malfunctions_description'  => ['type' => 'TEXT', 'null' => TRUE],
            
            'er_event_date'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            'er_event_start_work'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            'er_event_finish_work'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            
            'er_event_start_travel'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            'er_travel_time'               => ['type' => 'BIGINT', 'null' => TRUE],
            'er_on_site_time'              => ['type' => 'BIGINT', 'null' => TRUE],
            
            'er_event_status_work'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            'er_team_fail_equipment'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE], 
            'er_event_payment_amount'      => ['type' => 'TEXT', 'null' => TRUE],
        ]);

        $this->dbforge->add_key('er_id', TRUE);
        $this->dbforge->create_table('events_reports', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('events_reports', TRUE);
    }

}