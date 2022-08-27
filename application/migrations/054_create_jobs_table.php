<?php

class Migration_create_jobs_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'job_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'job_driver' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),
            'job_payload' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE
            ),
            'job_attempts' => array(
                'type' => 'INT',
                'default' => 0
            ),
            'job_is_completed' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ),
            'job_available_at' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'job_reserved_at' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'job_created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('job_id', TRUE);
        $this->dbforge->add_key('job_is_completed');
        $this->dbforge->add_key('job_reserved_at');
        $this->dbforge->add_key('job_available_at');
        $this->dbforge->add_key('job_attempts');
        $this->dbforge->create_table('jobs');
    }

    public function down() {
        $this->dbforge->drop_table('jobs', true);
    }

}