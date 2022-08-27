<?php

class Migration_add_fields_to_payment_transactions_and_client_payments_tables extends CI_Migration {

    public function up() {
        $this->dbforge->add_column('payment_transactions', array(
            'payment_transaction_remote_reason_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),

            'payment_transaction_remote_reason_description' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'payment_transaction_remote_status' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'payment_transaction_settled_amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => TRUE,
            ),
        ));
    }

    public function down() {
        $this->dbforge->drop_column('payment_transactions','payment_transaction_remote_reason_code');
        $this->dbforge->drop_column('payment_transactions','payment_transaction_remote_reason_description');
        $this->dbforge->drop_column('payment_transactions','payment_transaction_remote_status');
        $this->dbforge->drop_column('payment_transactions','payment_transaction_settled_amount');
    }

}