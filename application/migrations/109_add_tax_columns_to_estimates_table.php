<?php

class Migration_add_tax_columns_to_estimates_table extends CI_Migration {

    public function up() {
        $fields = [
            'estimate_tax_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => config_item('tax_name'),
            ],
            'estimate_tax_rate' => [
                'type' =>'FLOAT',
                'default' => config_item('tax_perc') / 100 + 1,
            ],
            'estimate_tax_value' => [
                'type' =>'FLOAT',
                'default' => config_item('tax_perc'),
            ]
        ];
        $this->dbforge->add_column('estimates', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('estimates', 'estimate_tax_name');
        $this->dbforge->drop_column('estimates', 'estimate_tax_rate');
        $this->dbforge->drop_column('estimates', 'estimate_tax_value');
    }

}