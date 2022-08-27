<?php

class Migration_change_type_invoice_status_field extends CI_Migration {

     public function up() {
        $fields = array(
            'in_status' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('invoices', $fields);
    }

    public function down() {
        $fields = array(
            'in_status' => array(
                'type' => 'VARCHAR',
                'constraint' => "150",
                'null' => FALSE,
            )
        );

        
        $this->dbforge->modify_column('invoices', $fields);
    }
    

}
