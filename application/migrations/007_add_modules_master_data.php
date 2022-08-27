<?php

class Migration_add_modules_master_data extends CI_Migration {

    public function up() {
        $data = ['module_id' => 'CHNGESTS', 'module_desc' => 'Change Estimators'];
        $this->db->insert('modules_master', $data);
    }

    public function down() {
        $where = ['module_id' => 'CHNGESTS', 'module_desc' => 'Change Estimators'];
        $this->db->where($where);
        $this->db->delete('modules_master');
        echo $this->db->last_query();
    }

}