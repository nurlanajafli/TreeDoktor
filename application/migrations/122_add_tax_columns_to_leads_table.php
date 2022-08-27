<?php

class Migration_add_tax_columns_to_leads_table extends CI_Migration {

    public function up() {
        $fields = [
            'lead_tax_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => null,
            ],
            'lead_tax_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '7,4',
                'default' => '1.0000',
            ],
            'lead_tax_value' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
            ]
        ];
        $this->dbforge->add_column('leads', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('leads', 'lead_tax_name');
        $this->dbforge->drop_column('leads', 'lead_tax_rate');
        $this->dbforge->drop_column('leads', 'lead_tax_value');
    }

}