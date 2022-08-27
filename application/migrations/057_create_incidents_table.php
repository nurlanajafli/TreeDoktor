<?php

class Migration_create_incidents_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'inc_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'inc_user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'inc_job_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'inc_payload' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE
            ),
            'inc_created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('inc_id', TRUE);
        $this->dbforge->add_key('inc_user_id');
        $this->dbforge->add_key('inc_job_id');
        $this->dbforge->add_key('inc_created_at');
        $this->dbforge->create_table('incidents');
    }

    public function down() {
        $this->dbforge->drop_table('incidents', true);
    }

}