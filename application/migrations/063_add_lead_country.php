<?php

class Migration_add_lead_country extends CI_Migration {

    public function up() {
        $fields = [
            'lead_country' => [
                'type' => 'VARCHAR', 
                'constraint' => 50, 
                'null' => true,
                //'after'=>'lead_zip'
            ],
        ];
        
        $this->dbforge->add_column('leads', $fields, 'lead_zip');
    }

    public function down() {
        $this->dbforge->drop_column('leads', 'lead_country');
    }

}