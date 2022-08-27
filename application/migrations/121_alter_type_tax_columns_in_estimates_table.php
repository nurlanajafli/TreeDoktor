<?php

class Migration_alter_type_tax_columns_in_estimates_table extends CI_Migration {

    public function up() {
        $fields = [
            'estimate_tax_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => config_item('tax_name'),
            ],
            'estimate_tax_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '7,4',
                'default' => config_item('tax_perc') / 100 + 1,
            ],
            'estimate_tax_value' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => config_item('tax_perc'),
            ]
        ];
        $this->dbforge->modify_column('estimates', $fields);
    }

    public function down() {
    }

}