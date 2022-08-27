<?php

class Migration_modify_payment_transactions_table extends CI_Migration {

    public function up() {
        $fields = [
            'payment_transaction_remote_id' => [
                'type' =>'BIGINT',
                'constraint' => 20,
                'null' => TRUE,
            ],
            'payment_transaction_card_num' => [
                'type' =>'VARCHAR',
                'constraint' => 20,
                'null' => TRUE,
            ],
        ];
        $this->dbforge->modify_column('payment_transactions', $fields);
        $this->dbforge->add_column('payment_transactions', array(
            'payment_transaction_auth_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
        ));
        $this->dbforge->drop_column('payment_transactions','payment_transaction_type');
    }

    public function down() {
        $fields = [
            'payment_transaction_remote_id' => [
                'type' =>'INT',
                'constraint' => 11,
                'null' => TRUE,
            ],
            'payment_transaction_card_num' => [
                'type' =>'INT',
                'constraint' => 11,
                'null' => TRUE,
            ],
        ];
        $this->dbforge->modify_column('payment_transactions', $fields);
        $this->dbforge->add_column('payment_transactions', array(
            'payment_transaction_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
        ));
        $this->dbforge->drop_column('payment_transactions','payment_transaction_auth_code');
    }

}