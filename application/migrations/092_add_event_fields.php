<?php

class Migration_add_event_fields extends CI_Migration {

    public function up() {
        $fields = [
            'ev_start_work' => ['type' => 'DATETIME', 'null' => TRUE],
            'ev_end_work' => ['type' => 'DATETIME', 'null' => TRUE],
            'ev_start_travel' => ['type' => 'DATETIME', 'null' => TRUE],
            'ev_travel_time' => ['type' => 'BIGINT', 'null' => TRUE],
            'ev_on_site_time' => ['type' => 'BIGINT', 'null' => TRUE],
        ];
        $this->dbforge->add_column('events', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('events', 'ev_start_work');
        $this->dbforge->drop_column('events', 'ev_end_work');
        $this->dbforge->drop_column('events', 'ev_start_travel');
        $this->dbforge->drop_column('events', 'ev_travel_time');
        $this->dbforge->drop_column('events', 'ev_on_site_time');
    }

}