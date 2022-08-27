<?php

class Migration_settings_estimates_overhead extends CI_Migration {

    public function up() {
        $data['stt_key_name'] = 'service_overhead_rate';
        $data['stt_key_value'] = '0';
        $data['stt_key_validate'] = 'numeric|floatval';
        $data['stt_section'] = 'Prices';
        $data['stt_label'] = 'Service Overhead Rate';

        $this->db->insert('settings', $data);
    }

    public function down() {
        $this->db->where(['stt_key_name' => "service_overhead_rate"]);
        $this->db->delete('settings');
    }

}
