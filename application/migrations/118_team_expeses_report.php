<?php

class Migration_team_expeses_report extends CI_Migration {

    public function up() {
        
        $this->dbforge->add_field([
            'ter_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'ter_team_id'=>[
                'type' => 'INT',
                'constraint' => 11,
            ],
            'ter_user_id'=>[
                'type' => 'INT',
                'constraint' => 11,
            ],
            'ter_bld' => [
                'type' => 'DECIMAL', 
                'constraint' => '10,2', 
                'default' => 0
            ],
            'ter_extra' => [
                'type' => 'DECIMAL', 
                'constraint' => '10,2', 
                'default' => 0
            ],
            'ter_extra_comment'  => [
                'type' => 'TEXT', 
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('ter_id', TRUE);
        $this->dbforge->create_table('team_expeses_report', TRUE);
        
        $sql = "CREATE INDEX ter_team_id ON team_expeses_report(ter_team_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_table('team_expeses_report', TRUE);
    }

}
