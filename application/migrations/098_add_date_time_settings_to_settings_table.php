<?php

class Migration_add_date_time_settings_to_settings_table extends CI_Migration {

    public function up() {
        $settings[] = [
            'stt_key_name' => 'allTimeFormats',
            'stt_key_value' => json_encode(["12", "24"]),
            'stt_key_validate' => '',
            'stt_section' => 'Date format',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'allDateFormat',
            'stt_key_value' => json_encode(["Y-m-d","m/d/Y","d/m/Y","M d, Y","d M Y"]),
            'stt_key_validate' => '',
            'stt_section' => 'Date format',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'dateFormat',
            'stt_key_value' => 'Y-m-d',
            'stt_key_validate' => '',
            'stt_section' => 'Date format',
            'stt_label' => 'Date format',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => "class='select2 w-100' id='dateFormats'"
        ];
        $settings[] = [
            'stt_key_name' => 'time',
            'stt_key_value' => '12',
            'stt_key_validate' => '',
            'stt_section' => 'Date format',
            'stt_label' => 'Time format',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => "class='select2 w-100' id='timeFormats'"
        ];
        foreach ($settings as $setting)
            $this->db->insert('settings', $setting);
    }

    public function down() {
        $settings = [
            'allTimeFormats', 'allDateFormat', 'dateFormat', 'time'
        ];
        foreach ($settings as $setting) {
            $this->db->where(['stt_key_name' => $setting]);
            $this->db->delete('settings');
        }
    }

}
