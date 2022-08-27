<?php

class Migration_add_qb_invoice_id_column_to_invoices extends CI_Migration {

    public function up() {
        $fields = array(
            'invoice_qb_id' => array(
                'type' => 'INT',
                'null' => true
            ),
        );
        $this->dbforge->add_column('invoices', $fields);
        $sql = "CREATE INDEX invoice_qb_id ON invoices(invoice_qb_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('invoices', 'invoice_qb_id');
    }

}