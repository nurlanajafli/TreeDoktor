<?php

class Migration_add_expense_team_id extends CI_Migration {

    public function up() {
        $this->dbforge->add_column('expenses', array(
            'expense_team_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                /*'after'=>'expense_user_id'*/
            ],
            'expense_is_extra' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ), 'expense_user_id');

        $sql = "CREATE INDEX expense_team_id ON expenses(expense_team_id)";
        $this->db->query($sql);

        $sql = "CREATE INDEX expense_is_extra ON expenses(expense_is_extra)";
        $this->db->query($sql);
        
    }

    public function down() {
        $this->dbforge->drop_column('expenses','expense_team_id', TRUE);
        $this->dbforge->drop_column('expenses','expense_is_extra', TRUE);
    }

}