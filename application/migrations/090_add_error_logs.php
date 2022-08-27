<?php

class Migration_add_error_logs extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'el_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'el_error_hash' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ),
            'el_cteated_time' => array(
                'type' => 'BIGINT',
                'null' => TRUE
            )
        ));
        $this->dbforge->add_key('el_id', TRUE);
        $this->dbforge->create_table('error_logs');
    }

    public function down() {
        $this->dbforge->drop_table('error_logs', true);
    }

}