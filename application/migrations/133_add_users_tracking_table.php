<?php

class Migration_add_users_tracking_table extends CI_Migration {

    public function up() {
        
        $this->dbforge->add_field([
            'ut_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],            
            'ut_user_id'=>[
                'type' => 'INT',
                'constraint' => 11,
            ],
            'ut_date' => [
                'type' => 'datetime', 
                'null' => false
            ],
            'ut_lat' => [
                'type' => 'double', 
                'null' => false
            ],
            'ut_lng'  => [
                'type' => 'double', 
                'null' => false
            ]
        ]);
        $this->dbforge->add_key('ut_id', TRUE);
        $this->dbforge->create_table('users_tracking', TRUE);
        
        $sql = "CREATE INDEX ut_user_id ON users_tracking(ut_user_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('users_tracking', TRUE);
    }

}
