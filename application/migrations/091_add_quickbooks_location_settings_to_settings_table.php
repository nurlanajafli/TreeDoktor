<?php

class Migration_add_quickbooks_location_settings_to_settings_table extends CI_Migration {

    public function up() {
        $settings[] = [
            'stt_key_name' => 'QBLocations',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'Location',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Location',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => "id='location' class='select2 w-70' disabled='disabled'"
        ];
        foreach ($settings as $setting)
            $this->db->insert('settings', $setting);
    }

    public function down() {
        $settings = [
            'QBLocations', 'Location'
        ];
        foreach ($settings as $setting) {
            $this->db->where(['stt_key_name' => $setting]);
            $this->db->delete('settings');
        }
    }

}