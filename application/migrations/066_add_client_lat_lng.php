<?php

class Migration_add_client_lat_lng extends CI_Migration {

    public function up() {
        $fields = [
            'client_lat' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'client_lng' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
        ];
        
        $this->dbforge->add_column('clients', $fields, 'client_country');
    }

    public function down() {

        $this->dbforge->drop_column('clients', 'client_lat');
        $this->dbforge->drop_column('clients', 'client_lng');
        
    }

}