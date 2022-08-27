<?php

class Migration_add_tax_rate extends CI_Migration {

    public function up() {
        $data['stt_key_name'] = 'tax_rate';
        $data['stt_key_value'] = '1';
        $data['stt_key_validate'] = 'numeric|floatval';
        $data['stt_section'] = 'Prices';
        $data['stt_label'] = 'Tax Rate';
        
        $this->db->insert('settings', $data);
    }

    public function down() {
        $this->db->where(['stt_key_name' => "tax_rate"]);
        $this->db->delete('settings');
    }

}
