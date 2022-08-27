<?php

class Migration_add_qb_service_id_column_to_services extends CI_Migration {

    public function up() {
        $fields = array(
            'service_qb_id' => array(
                'type' => 'INT',
                'null' => true
            ),
        );
        $this->dbforge->add_column('services', $fields);
        $sql = "CREATE INDEX service_qb_id ON services(service_qb_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('services', 'service_qb_id');
    }

}



