<?php

class Migration_add_invoices_statuses_flags extends CI_Migration {

    public function up() {
        $fields = [
            'default' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_hold_backs' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_sent' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_overdue' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'completed' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'protected' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ];
        
        $this->dbforge->add_column('invoice_statuses', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('invoice_statuses', 'default');
        $this->dbforge->drop_column('invoice_statuses', 'is_hold_backs');
        $this->dbforge->drop_column('invoice_statuses', 'is_sent');
        $this->dbforge->drop_column('invoice_statuses', 'is_overdue');
        $this->dbforge->drop_column('invoice_statuses', 'completed');
    }

}