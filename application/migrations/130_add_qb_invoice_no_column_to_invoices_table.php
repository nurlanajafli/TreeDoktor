<?php

class Migration_add_qb_invoice_no_column_to_invoices_table extends CI_Migration {

    public function up() {
        $fields = array(
            'qb_invoice_no' => array(
                'type' => 'VARCHAR',
                'constraint' => 20
            ),
        );
        $this->dbforge->add_column('invoices', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('invoices', 'qb_invoice_no');
    }

}