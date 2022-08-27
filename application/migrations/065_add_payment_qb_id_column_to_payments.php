<?php

class Migration_add_payment_qb_id_column_to_payments extends CI_Migration {

    public function up() {
        $fields = array(
            'payment_qb_id' => array(
                'type' => 'INT',
                'null' => true
            ),
        );
        $this->dbforge->add_column('client_payments', $fields);
        $sql = "CREATE INDEX payment_qb_id ON client_payments(payment_qb_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('client_payments', 'payment_qb_id');
    }

}