<?php

class Migration_add_sms_system_label extends CI_Migration {

    public function up() {
        $fields = array(
            'system_label' => array('type' => 'VARCHAR', 'constraint' => 150,  'null' => TRUE)
        );
        $this->dbforge->add_column('sms_tpl', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('sms_tpl', 'system_label');
    }

}