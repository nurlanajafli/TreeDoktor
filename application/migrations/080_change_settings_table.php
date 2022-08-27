<?php

class Migration_change_settings_table extends CI_Migration {

    public function up() {
        $field = array(
            'stt_html_attrs' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
        );
        $this->dbforge->add_column('settings', $field);
    }

    public function down() {
        $this->dbforge->drop_column('settings', 'stt_html_attrs');
    }

}