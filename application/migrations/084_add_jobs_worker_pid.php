<?php

class Migration_add_jobs_worker_pid extends CI_Migration {

    public function up() {
        $fields = [
            'job_worker_pid' => [
                'type' =>'INT',
                'constraint' => 11,
                'null' => TRUE,
            ],
        ];

        $this->dbforge->add_column('jobs', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('jobs', 'job_worker_pid');
    }

}
