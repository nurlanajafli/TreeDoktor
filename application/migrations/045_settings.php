<?php

class Migration_settings extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'stt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'stt_key_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'stt_key_value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'stt_key_validate' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'stt_section' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'stt_label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
        ));
        $this->dbforge->add_key('stt_id', TRUE);
        $this->dbforge->create_table('settings');
    }

    public function down() {
        $this->dbforge->drop_table('settings', true);
    }

}