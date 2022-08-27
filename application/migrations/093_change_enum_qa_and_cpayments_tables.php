<?php

class Migration_change_enum_qa_and_cpayments_tables extends CI_Migration {

    public function up() {
        $field = array(
            'payment_method_int' => array(
                'type' => 'TINYINT',
                'constraint' => 4,
                'null' => FALSE,
            ),
        );
        $this->dbforge->add_column('client_payments', $field);
        
        $this->db->query('update client_payments  set payment_method_int = 1 WHERE payment_method = "cash"');
        $this->db->query('update client_payments  set payment_method_int = 2 WHERE payment_method = "cc"');
        $this->db->query('update client_payments  set payment_method_int = 3 WHERE payment_method = "check"');
        $this->db->query('update client_payments  set payment_method_int = 4 WHERE payment_method = "dc"');
        $this->db->query('update client_payments  set payment_method_int = 5 WHERE payment_method = "etransfer"');
        
        $this->dbforge->drop_column('client_payments', 'payment_method');
        
        $field = array(
            'qa_type_int' => array(
                'type' => 'TINYINT',
                'constraint' => 4,
                'null' => FALSE,
            ),
        );
        $this->dbforge->add_column('qa', $field);
        
        $this->db->query('update qa  set qa_type_int = 1 WHERE qa_type = "suggestion"');
        $this->db->query('update qa  set qa_type_int = 2 WHERE qa_type = "complaint"');
        $this->db->query('update qa  set qa_type_int = 2 WHERE qa_type = "complain"');
        $this->db->query('update qa  set qa_type_int = 3 WHERE qa_type = "compliment"');
        $this->db->query('update qa  set qa_type_int = 3 WHERE qa_type = "complement"');

        $this->dbforge->drop_column('qa', 'qa_type');
    }

    public function down() {
         $field = array(
            'payment_method' => array(
                'type' => 'ENUM',
                'constraint' => ["cash","cc","check","dc","etransfer"],
                'default' => 'cash',
                'null' => FALSE,
            ),
        );
        $this->dbforge->add_column('client_payments', $field);
        
        $this->db->query('update client_payments  set payment_method = "cash" WHERE payment_method_int = 1');
        $this->db->query('update client_payments  set  payment_method = "cc" WHERE payment_method_int = 2');
        $this->db->query('update client_payments  set  payment_method = "check" WHERE payment_method_int = 3');
        $this->db->query('update client_payments  set payment_method = "dc" WHERE payment_method_int = 4');
        $this->db->query('update client_payments  set payment_method = "etransfer" WHERE payment_method_int = 5');
        
        $this->dbforge->drop_column('client_payments', 'payment_method_int');
        
        $field = array(
            'qa_type' => array(
                'type' => 'ENUM',
                'constraint' => ["suggestion","complaint","compliment"],
                'default' => 'complaint',
                'null' => FALSE,
            ),
        );
        $this->dbforge->add_column('qa', $field);
        
        $this->db->query('update qa  set qa_type = "suggestion" WHERE qa_type_int = 1');
        $this->db->query('update qa  set qa_type = "complaint" WHERE qa_type_int = 2');
        $this->db->query('update qa  set qa_type = "compliment" WHERE qa_type_int = 3');
        
        $this->dbforge->drop_column('qa', 'qa_type_int');
    }

}
