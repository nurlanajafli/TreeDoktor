<?php

class Migration_add_estimate_services_field extends CI_Migration {

    public function up() {
        
        $field = [
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true
            ]
        ];
        $this->dbforge->add_column('estimates_services', $field);
    }

    public function down() {
        $this->dbforge->drop_column('estimates_services', 'quantity');
        $this->dbforge->drop_column('estimates_services', 'cost');
    }

}