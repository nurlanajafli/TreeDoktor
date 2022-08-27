<?php

class Migration_add_invoices_field extends CI_Migration {

    public function up() {
        $fields = array(
			'overpaid' => array('type' => 'TINYINT', 'constraint' => 1,  'null' => TRUE)
		);
		$this->dbforge->add_column('invoices', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('invoices', 'overpaid');
    }

}
