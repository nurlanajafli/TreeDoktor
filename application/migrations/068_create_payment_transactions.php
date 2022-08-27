<?php

class Migration_create_payment_transactions extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'payment_transaction_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'payment_transaction_status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ),
            'client_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ),
            'estimate_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ),
            'invoice_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'payment_driver' => array(
                'type' => 'varchar',
                'constraint' => 255,
                'null' => false,
            ),
            'payment_transaction_remote_id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => TRUE,
            ),
            'payment_transaction_amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'null' => false,
            ),
            'payment_transaction_approved' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ),
            'payment_transaction_risk' => array(
                'type' => 'FLOAT',
                'constraint' => '5,2',
                'default' => 0,
                'null' => false,
            ),
            'payment_transaction_order_no' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'payment_transaction_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'payment_transaction_card' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'payment_transaction_card_num' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'null' => TRUE,
            ),
            'payment_transaction_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'payment_transaction_message' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'payment_transaction_log' => array(
                'type' => 'JSON',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('payment_transaction_id', TRUE);
        $this->dbforge->add_key('payment_transaction_status', TRUE);
        $this->dbforge->create_table('payment_transactions');
    }

    public function down() {
        $this->dbforge->drop_table('payment_transactions', true);
    }

}