<?php

class Migration_add_sync_qb_column_to_invoice_interest extends CI_Migration {

    public function up() {
        $fields = array(
            'sync_qb' => array(
                'type' => 'INT',
                'default' => 0
            ),
        );
        $this->dbforge->add_column('invoice_interest', $fields);
        $sql = "CREATE INDEX sync_qb ON invoice_interest(sync_qb)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('invoice_interest', 'sync_qb');
    }
}