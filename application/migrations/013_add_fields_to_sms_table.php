<?php

class Migration_add_fields_to_sms_table extends CI_Migration {

    public function up() {
        $fields = array(
			'user' => array('type' => 'TINYINT', 'constraint' => 1,  'null' => TRUE)
		);
		$this->dbforge->add_column('sms_tpl', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('sms_tpl', 'user');
    }

}
