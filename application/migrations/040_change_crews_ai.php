<?php

class Migration_change_crews_ai extends CI_Migration {

    public function up() {
        $this->db->where('crew_name', 'Day Off');
        $this->db->update('crews', ['crew_id' => 0]);
    }

    public function down() {

    }

}