<?php

class Migration_add_payment_type_and_ref_id_to_payment_transactions_table extends CI_Migration {

    public function up() {
        $fields = array(
            'payment_transaction_ref_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ),
            'payment_transaction_type' => array(
                'type' => 'ENUM',
                'constraint' => ["payment","refund","void"],
                'default' => 'payment',
                'null' => FALSE
            ),
        );
        $this->dbforge->add_column('payment_transactions', $fields);
        $sql = "CREATE INDEX payment_transaction_ref_id ON payment_transactions(payment_transaction_ref_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('payment_transactions', 'payment_transaction_ref_id');
        $this->dbforge->drop_column('payment_transactions', 'payment_transaction_type');
    }

}