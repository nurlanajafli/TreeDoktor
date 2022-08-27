<?php

class Migration_add_expense_event_id extends CI_Migration {

    public function up() {
        $fields = [
            'expense_event_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true
            ]
        ];
        $this->dbforge->add_column('expenses', $fields, 'expense_item_id');
        
        $sql = "CREATE INDEX expense_event_id ON expenses(expense_event_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('expenses', 'expense_event_id');
    }

}