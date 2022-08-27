<?php

class Migration_add_jobs_output extends CI_Migration {

    public function up() {
        $fields = [
            'job_output' => [
                'type' =>'LONGTEXT',
                'null' => TRUE
            ],
        ];

        $this->dbforge->add_column('jobs', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('jobs', 'job_output');
    }

}
