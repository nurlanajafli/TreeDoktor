<?php

class Migration_create_estimates_bundles_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'eb_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'eb_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'eb_bundle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ]
        ]);
        $this->dbforge->add_key('eb_id', TRUE);
        $this->dbforge->create_table('estimates_bundles');
        $sql = "CREATE INDEX eb_service_id ON estimates_bundles(eb_service_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('estimates_bundles');
    }

}