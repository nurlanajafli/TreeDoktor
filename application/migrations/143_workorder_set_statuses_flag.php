<?php

class Migration_workorder_set_statuses_flag extends CI_Migration {

    public function up() {
        $this->db->update('workorder_status', ['is_protected'=>1, 'is_confirm_by_client'=>1], ['wo_status_id'=>1]);
        $this->db->update('workorder_status', ['is_protected'=>1, 'is_default'=>1], ['wo_status_id'=>2]);
        $this->db->update('workorder_status', ['is_protected'=>1, 'is_finished_by_field'=>1], ['wo_status_id'=>7]);
        $this->db->update('workorder_status', ['is_protected'=>1, 'is_finished'=>1], ['wo_status_id'=>0]);
    }

    public function down() {
        $this->db->update('workorder_status', ['is_protected'=>0, 'is_default'=>0], ['wo_status_id'=>1]);
        $this->db->update('workorder_status', ['is_protected'=>0, 'is_default'=>0], ['wo_status_id'=>2]);
        $this->db->update('workorder_status', ['is_protected'=>0, 'is_finished_by_field'=>0], ['wo_status_id'=>7]);
        $this->db->update('workorder_status', ['is_protected'=>0, 'is_finished'=>0], ['wo_status_id'=>0]);
    }

}
