<?php

class Migration_add_bundle_flag_to_services_table extends CI_Migration {

    public function up() {
        $field = [
            'is_bundle' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=> 0
            ]
        ];
        $this->dbforge->add_column('services', $field);
        $sql = "CREATE INDEX is_bundle ON services(is_bundle)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('services', 'is_bundle');
    }

}