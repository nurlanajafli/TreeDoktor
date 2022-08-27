<?php

class Migration_add_stt_is_hidden_column_and_changed_type_stt_key_value extends CI_Migration {

    public function up() {
        $field = array(
            'stt_is_hidden' => array(
                'type' => 'BOOL',
                'default' => 0
            ),
        );
        $this->dbforge->add_column('settings', $field);

        $field = array(
            'stt_key_value' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE
            ),
        );
        $this->dbforge->modify_column('settings', $field);
    }

    public function down() {
        $this->dbforge->drop_column('settings', 'stt_is_hidden');
        $field = array(
            'stt_key_value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
        );
        $this->dbforge->modify_column('settings', $field);
    }

}