<?php

class Migration_add_payment_profile_id_to_clients extends CI_Migration {

    public function up() {
        $fields = array(
            'client_payment_profile_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ],
            'client_payment_driver' => [
            'type' => 'varchar',
            'constraint' => 255,
            'null' => TRUE,
        ]
        );

        $this->dbforge->add_column('clients', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('clients', 'client_payment_profile_id');
        $this->dbforge->drop_column('clients', 'client_payment_driver');
    }

}