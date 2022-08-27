<?php

class Migration_change_incident_job_id_type extends CI_Migration {

    public function up() {
        $fields = array(
            'inc_job_id' => array(
                'type' => 'BIGINT',
                'null' => TRUE
            ),
        );
        $this->dbforge->modify_column('incidents', $fields);
    }

    public function down() {
        $fields = array(
            'inc_job_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
        );
        $this->dbforge->modify_column('incidents', $fields);
    }

}
