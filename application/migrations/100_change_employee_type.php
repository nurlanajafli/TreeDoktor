<?php

class Migration_change_employee_type extends CI_Migration {

    public function up() {
        $field = array(
            'emp_type_new' => array(
                'type' => 'ENUM',
                'constraint' => ["employee","subcontractor","temp/cash"],
                'default' => 'employee',
                'null' => FALSE
            ),
        );
        $this->dbforge->add_column('employees', $field);
        $this->db->query('update employees  set emp_type_new = "employee" WHERE emp_type = "employee"');
        $this->db->query('update employees  set emp_type_new = "subcontractor" WHERE emp_type = "sub_ta"');
        $this->db->query('update employees  set emp_type_new = "temp/cash" WHERE emp_type = "sub_ca"');
        
        
        $this->dbforge->drop_column('employees', 'emp_type');
        $fields = array(
			'emp_type_new' => array(
					'name' => 'emp_type',
					'type' => 'ENUM',
					'constraint' => ["employee","subcontractor","temp/cash"],
					'default' => 'employee',
					'null' => FALSE
				),
		);
		$this->dbforge->modify_column('employees', $fields);
    }

    public function down() {
        $field = array(
            'emp_type_new' => array(
                'type' => 'ENUM',
                'constraint' => ["employee","sub_ta","sub_ca"],
                'default' => 'employee',
                'null' => FALSE
            ),
        );
        $this->dbforge->add_column('employees', $field);
        $this->db->query('update employees  set emp_type_new = "employee" WHERE emp_type = "employee"');
        $this->db->query('update employees  set emp_type_new = "sub_ta" WHERE emp_type = "subcontractor"');
        $this->db->query('update employees  set emp_type_new = "sub_ca" WHERE emp_type = "temp/cash"');
        
        
        $this->dbforge->drop_column('employees', 'emp_type');
        $fields = array(
			'emp_type_new' => array(
				'name' => 'emp_type',
				'type' => 'ENUM',
				'constraint' => ["employee","sub_ta","sub_ca"],
				'default' => 'employee',
				'null' => FALSE
			),
		);
		$this->dbforge->modify_column('employees', $fields);
    }

}
