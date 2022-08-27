<?php

class Migration_create_estimates_bundles_records extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'estimate_bundle_record_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'estimate_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'bundle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'record_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'estimate_bundle_record_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'null' => false,
            ],
            'estimate_bundle_record_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
            'estimate_bundle_record_description' => [
                'type' => 'text',
                'null' => false,
            ],
            'non_taxable' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ]);
        $this->dbforge->add_key('estimate_bundle_record_id', TRUE);
        $this->dbforge->create_table('estimates_bundles_records');
        $sql = "CREATE INDEX estimate_service_id ON estimates_bundles_records(estimate_service_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('estimates_bundles_records');
    }

}