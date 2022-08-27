<?php

class Migration_add_is_overpaid extends CI_Migration {

    public function up() {
        $fields = [
            'is_overpaid' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ];
        
        $this->dbforge->add_column('invoice_statuses', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('invoice_statuses', 'is_overpaid');
    }

}