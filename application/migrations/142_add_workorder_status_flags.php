<?php

class Migration_add_workorder_status_flags extends CI_Migration {

    public function up() {
        $fields = [
            'is_default' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_confirm_by_client' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_finished_by_field' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],

            'is_finished' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],

            'is_protected' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ];
        
        $this->dbforge->add_column('workorder_status', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('workorder_status', 'is_default');
        $this->dbforge->drop_column('workorder_status', 'is_confirm_by_client');
        $this->dbforge->drop_column('workorder_status', 'is_finished_by_field');
        $this->dbforge->drop_column('workorder_status', 'is_finished');
        $this->dbforge->drop_column('workorder_status', 'is_protected');
    }

}