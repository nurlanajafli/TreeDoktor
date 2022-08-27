<?php

class Migration_create_bundles_services_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'bundle_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'bundle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'null' => false,
            ]
        ]);
        $this->dbforge->add_key('bundle_service_id', TRUE);
        $this->dbforge->create_table('bundles_services');
        $sql = "CREATE INDEX bundle_id ON bundles_services(bundle_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('bundles_services');
    }

}