<?php

class Migration_add_services_product_flag extends CI_Migration {

    public function up() {
        $fields = [
            'is_product' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=> 0
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true
            ]
        ];
        
        $this->dbforge->add_column('services', $fields);
        $sql = "CREATE INDEX is_product ON services(is_product)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('services', 'is_product');
        $this->dbforge->drop_column('services', 'cost');
    }

}