<?php

class Migration_add_expense_tax_column_to_expenses_table extends CI_Migration {

    public function up() {
        $field = [
            'expense_tax' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => json_encode(['name' => config_item('tax_name'), 'value' => config_item('tax_perc')])
            ]
        ];
        $this->dbforge->add_column('expenses', $field);
    }

    public function down() {
        $this->dbforge->drop_column('expenses', 'expense_tax');
    }

}