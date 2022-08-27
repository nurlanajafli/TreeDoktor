<?php

class Migration_add_user_tracking_option extends CI_Migration
{

    public function up() {
        $fields = [
            'is_tracked' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false
            ]            
        ];
        $this->dbforge->add_column('users', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('users', 'is_tracked');        
    }

}