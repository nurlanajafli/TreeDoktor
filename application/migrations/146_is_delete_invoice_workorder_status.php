<?php

class Migration_is_delete_invoice_workorder_status extends CI_Migration {

    public function up() {
        $fields = [
            'is_delete_invoice' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ];
        
        $this->dbforge->add_column('workorder_status', $fields);

        $this->db->update('workorder_status', ['is_protected'=>1, 'is_delete_invoice'=>1], ['wo_status_id'=>10]);
    }

    public function down() {
        $this->db->update('workorder_status', ['is_protected'=>0, 'is_delete_invoice'=>0], ['wo_status_id'=>10]);

        $this->dbforge->drop_column('workorder_status', 'is_delete_invoice');
    }

}