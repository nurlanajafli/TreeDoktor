<?php

class Migration_add_invoices_statuses_priority extends CI_Migration {

    public function up() {
        $fields = [
            'priority' => [
                'type' =>'INT',
                'constraint' => 4,
                'default' => 0
            ],
        ];
        
        $this->dbforge->add_column('invoice_statuses', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('invoice_statuses', 'priority');
    }

}