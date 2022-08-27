<?php

class Migration_add_expense_types_flags_and_system extends CI_Migration {

    public function up() {
        $this->dbforge->add_column('expense_types', array(
            'flag' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'protected' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ));

        $this->db->like('expense_name', 'Employee benefits');
        $query = $this->db->get('expense_types');
        $expense_type = $query->row_array();

        if(is_array($expense_type) && count($expense_type)){
            $update = ['flag'=>'employee_benefits', 'protected'=>1];
            $this->db->update('expense_types', $update, ['expense_type_id' => $expense_type['expense_type_id']]);
        }
        else{
            $insert = [
                'expense_name'=>'Employee benefits',
                'expense_status'=>1,
                'flag'=>'employee_benefits', 
                'protected'=>1
            ];
            $this->db->insert('expense_types', $insert);
        }
    }

    public function down() {
        $this->dbforge->drop_column('expense_types','flag');
        $this->dbforge->drop_column('expense_types','protected');
    }

}