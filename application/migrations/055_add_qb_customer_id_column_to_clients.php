<?php

class Migration_add_qb_customer_id_column_to_clients extends CI_Migration {

    public function up() {
        $fields = array(
            'client_qb_id' => array(
                'type' => 'INT',
                'null' => true
            ),
        );
        $this->dbforge->add_column('clients', $fields);
        $sql = "CREATE INDEX client_qb_id ON clients(client_qb_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('clients', 'client_qb_id');
    }

}