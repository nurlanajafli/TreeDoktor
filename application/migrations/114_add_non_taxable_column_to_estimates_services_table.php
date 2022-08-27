<?php

class Migration_add_non_taxable_column_to_estimates_services_table extends CI_Migration {

    public function up() {
        $fields = [
            'non_taxable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=> 0
            ]
        ];
        $this->dbforge->add_column('estimates_services', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('estimates_services', 'non_taxable');
    }

}