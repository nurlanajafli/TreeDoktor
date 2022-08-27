<?php

class Migration_create_invoice_statuses extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'invoice_status_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
			'invoice_status_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			),
			'invoice_status_active' => array(
				'type' =>'TINYINT',
				'constraint' => 1,
				'default' => 1,
			)
        ));
        $this->dbforge->add_key('invoice_status_id', TRUE);
        
        
        
        $this->dbforge->create_table('invoice_statuses');
    }

    public function down() {
        $this->dbforge->drop_table('invoice_statuses', true);
    }

}
