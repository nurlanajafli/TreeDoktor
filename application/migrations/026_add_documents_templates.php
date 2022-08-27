<?php

class Migration_add_documents_templates extends CI_Migration {

    public function up() {
        $fields = array(
            'us_sms_tmp_id' => array('type' => 'INT', 'constraint' => 11,  'default' => 0),
            'us_email_tmp_id' => array('type' => 'INT', 'constraint' => 11,  'default' => 0)
        );

        $this->dbforge->add_column('user_certificates', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('user_certificates', 'us_sms_tmp_id');
        $this->dbforge->drop_column('user_certificates', 'us_email_tmp_id');
    }

}